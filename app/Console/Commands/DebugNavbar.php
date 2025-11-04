<?php

namespace App\Console\Commands;

use App\Models\Auth\User;
use App\Services\Core\ModuleService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DebugNavbar extends Command
{
    protected $signature = 'menu:debug {user : User ID or email}';
    protected $description = 'Print the visible navbar modules for a user (for RBAC debugging).';

    public function handle(): int
    {
        $identifier = (string) $this->argument('user');
        $user = is_numeric($identifier)
            ? User::query()->find($identifier)
            : User::query()->where('email', $identifier)->first();

        if (!$user) {
            $this->error('User not found. Provide a valid UserID or email.');
            return self::FAILURE;
        }

        // Clear navbar cache and build
        ModuleService::clearNavbarCache($user);

        $this->info('Building navbar for user: ' . ($user->email ?? $user->UserID));
        $html = ModuleService::generateNavbar($user);

        // Best-effort plain text extraction of module names from HTML
        // Looks for data-item-id attributes and adjacent text labels
        $text = strip_tags($html);
        $lines = array_filter(array_map('trim', preg_split('/\r?\n/', $text)));

        // Print compact output
        $this->line('--- Visible Modules (approx) ---');
        foreach ($lines as $ln) {
            if ($ln === '') continue;
            $this->line('- ' . Str::of($ln)->squish()->toString());
        }

        $this->newLine();
        $this->comment('Tip: Ensure the role has at least one permission within the target module (read/view or any action).');
        return self::SUCCESS;
    }
}
