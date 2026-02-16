<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedGrnMappingCommand extends Command
{
    protected $signature = 'finance:seed-grn-mapping {--force : Run the seeder in production}';

    protected $description = 'Seed GRN transaction types and GL mappings (includes GRN-SERVICE)';

    public function handle(): int
    {
        $this->info('Seeding GRN transaction types and GL mappings...');

        $params = [
            '--class' => \Database\Seeders\GRNTransactionTypesSeeder::class,
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $exit = Artisan::call('db:seed', $params);

        $output = Artisan::output();
        if ($output) {
            $this->line($output);
        }

        if ($exit === 0) {
            $this->info('GRN mapping seed completed successfully.');
        } else {
            $this->error('GRN mapping seed failed.');
        }

        return $exit;
    }
}
