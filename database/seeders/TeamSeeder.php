<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date = now();
        $user = SystemHelper::user();
        DB::table('t_Teams')->insert([
                                      'Name' => 'Marketing Team',
                                      'IsMarketing' => true,
                                      'Email' => config('org.email'),
                                      'CreatedOn' => $date,
                                      'CreatedBy' => $user->Id,
                                      'ModifiedOn' => $date,
                                      'ModifiedBy' => $user->Id,
                                     ]);
    }
}
