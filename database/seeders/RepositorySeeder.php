<?php

namespace Database\Seeders;

use App\Services\DMS\RepositoryService;
use Illuminate\Database\Seeder;

class RepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RepositoryService::root();
    }
}
