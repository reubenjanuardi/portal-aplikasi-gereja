<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historically written as UTC (config app.timezone was UTC).
     * Shift wall-clock values +7h so they match WIB after switching
     * app timezone to Asia/Jakarta.
     *
     * Only tables with a single-column primary key are included;
     * pivot/int-unix tables (cache, sessions) are intentionally skipped.
     */
    private const TARGETS = [
        'users' => ['key' => 'id', 'columns' => ['email_verified_at', 'created_at', 'updated_at', 'last_login_at']],
        'password_reset_tokens' => ['key' => 'email', 'columns' => ['created_at']],
        'activity_logs' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'vouchers' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'transactions' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'chart_of_accounts' => ['key' => 'kode_akun', 'columns' => ['created_at', 'updated_at']],
        'app_settings' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'permissions' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'roles' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'jobs' => ['key' => 'id', 'columns' => ['created_at', 'updated_at']],
        'failed_jobs' => ['key' => 'id', 'columns' => ['created_at', 'updated_at', 'failed_at']],
    ];

    public function up(): void
    {
        $this->shift(+7);
    }

    public function down(): void
    {
        $this->shift(-7);
    }

    private function shift(int $hours): void
    {
        foreach (self::TARGETS as $table => $config) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $key = $config['key'];
            $columns = array_values(array_filter(
                $config['columns'],
                fn (string $column) => Schema::hasColumn($table, $column)
            ));

            if ($columns === [] || ! Schema::hasColumn($table, $key)) {
                continue;
            }

            DB::table($table)
                ->chunkById(200, function ($rows) use ($table, $columns, $key, $hours) {
                    foreach ($rows as $row) {
                        $updates = [];

                        foreach ($columns as $column) {
                            if ($row->{$column} === null || $row->{$column} === '') {
                                continue;
                            }

                            $updates[$column] = Carbon::parse($row->{$column}, 'UTC')
                                ->addHours($hours)
                                ->format('Y-m-d H:i:s');
                        }

                        if ($updates !== []) {
                            DB::table($table)
                                ->where($key, $row->{$key})
                                ->update($updates);
                        }
                    }
                }, column: $key);
        }
    }
};
