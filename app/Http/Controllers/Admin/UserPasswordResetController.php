<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;

class UserPasswordResetController extends Controller
{
    public function generate(Request $request, User $user): JsonResponse
    {
        $admin = $request->user();

        if (! $user->is_active) {
            abort(403, 'Tidak dapat membuat link reset password untuk akun yang dinonaktifkan.');
        }

        if (! Gate::forUser($admin)->allows('resetPassword', $user)) {
            abort(403, 'Anda tidak memiliki izin untuk membuat link reset password untuk pengguna ini.');
        }

        $token = Password::broker()->createToken($user);
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $adminName = $admin?->name ?? 'Administrator';

        ActivityLog::log(
            description: "Administrator [{$adminName}] membuat link reset password untuk pengguna [{$user->name}]",
            logName: 'user_management',
            subject: $user,
            properties: [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
            ]
        );

        return response()->json([
            'success' => true,
            'url' => $resetUrl,
            'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
            'target' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
