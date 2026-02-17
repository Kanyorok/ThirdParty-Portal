<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClearPermissionCacheWithSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:cache-reset-with-sessions 
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear permission cache and invalidate all user sessions to ensure fresh permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will log out all users. Do you want to continue?')) {
                $this->warn('Operation cancelled');

                return 1;
            }
        }

        $this->info('Starting permission cache and session cleanup...');
        $this->newLine();

        // Step 1: Clear Spatie permission cache
        $this->info('[1/4] Clearing Spatie permission cache...');
        Artisan::call('permission:cache-reset');
        $this->line('   ✓ Permission cache cleared');

        // Step 2: Clear application cache (includes permission lookups)
        $this->info('[2/4] Clearing application cache...');
        Cache::flush();
        $this->line('   ✓ Application cache cleared');

        // Step 3: Clear all active sessions
        $this->info('[3/4] Invalidating user sessions...');
        $deletedCount = $this->clearSessions();
        $this->line("   ✓ Cleared {$deletedCount} active session(s)");

        // Step 4: Clear Gate policy cache
        $this->info('[4/4] Clearing authorization gate cache...');
        // The gate will rebuild automatically on next request
        $this->line('   ✓ Gate policies will be refreshed on next request');

        $this->newLine();
        $this->info('✅ Permission cache cleanup complete!');
        $this->newLine();
        $this->warn('⚠️  All users must login again to load fresh permissions');
        $this->newLine();

        return 0;
    }

    /**
     * Clear sessions based on the configured driver
     *
     * @return int Number of sessions cleared
     */
    protected function clearSessions(): int
    {
        $driver = config('session.driver');
        $deletedCount = 0;

        switch ($driver) {
            case 'database':
                // For database sessions, delete from the sessions table
                $table = config('session.table', 'sessions');
                $connection = config('session.connection');

                try {
                    $deletedCount = DB::connection($connection)
                        ->table($table)
                        ->delete();
                } catch (\Exception $e) {
                    $this->error("   ✗ Failed to clear database sessions: {$e->getMessage()}");
                }

                break;

            case 'redis':
                // For Redis sessions, flush the Redis database
                try {
                    $redis = app('redis')->connection(config('session.connection'));
                    $prefix = config('session.cookie', 'laravel_session');
                    $keys = $redis->keys("{$prefix}:*");

                    if (! empty($keys)) {
                        $redis->del($keys);
                        $deletedCount = count($keys);
                    }
                } catch (\Exception $e) {
                    $this->error("   ✗ Failed to clear Redis sessions: {$e->getMessage()}");
                }

                break;

            case 'file':
                // For file sessions, delete session files
                try {
                    $sessionPath = config('session.files', storage_path('framework/sessions'));
                    $files = glob($sessionPath . '/*');

                    if ($files) {
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                                $deletedCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("   ✗ Failed to clear file sessions: {$e->getMessage()}");
                }

                break;

            default:
                $this->warn("   ⚠ Session driver '{$driver}' - attempting generic clear");

                try {
                    Artisan::call('cache:clear');
                    $this->line('   ✓ Cleared cache (sessions may still be active)');
                } catch (\Exception $e) {
                    $this->error("   ✗ Failed to clear cache: {$e->getMessage()}");
                }
        }

        return $deletedCount;
    }
}
