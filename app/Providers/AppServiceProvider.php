<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, function () {
            return new class implements \Filament\Auth\Http\Responses\Contracts\LogoutResponse {
                public function toResponse($request): \Symfony\Component\HttpFoundation\Response
                {
                    return redirect('/');
                }
            };
        });

        if (! class_exists(\Dom\HTMLDocument::class)) {
            $this->app->scoped(HtmlSanitizerInterface::class, function () {
                return new class implements HtmlSanitizerInterface {
                    public function sanitize(string $input): string
                    {
                        return $input;
                    }

                    public function sanitizeFor(string $element, string $input): string
                    {
                        return $input;
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Super Admin bypass all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Activity Logging for Authentication Events
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if ($event->user instanceof \App\Models\User) {
                $event->user->updateQuietly([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                ]);
                \App\Models\ActivityLog::log(
                    description: "Pengguna [{$event->user->name}] berhasil login ke sistem",
                    logName: 'auth',
                    subject: $event->user
                );
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user instanceof \App\Models\User) {
                \App\Models\ActivityLog::log(
                    description: "Pengguna [{$event->user->name}] melakukan logout",
                    logName: 'auth',
                    subject: $event->user
                );
            }
        });

        Vite::prefetch(concurrency: 3);

        if (! class_exists(\Dom\HTMLDocument::class)) {
            Str::macro('sanitizeHtml', function (string $html): string {
                return $html;
            });

            Stringable::macro('sanitizeHtml', function (): Stringable {
                return $this;
            });
        }
    }
}
