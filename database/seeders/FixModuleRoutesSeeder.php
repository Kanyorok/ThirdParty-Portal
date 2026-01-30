<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixModuleRoutesSeeder extends Seeder
{
    public function run(): void
    {
        $updates = [
            // Avoid parameterized routes in navbar modules
            'bancassurance.medicalfunds.update' => 'bancassurance.medicalfunds.index',
            'bancassurance.medicalfunds.edit' => 'bancassurance.medicalfunds.index',

            // Fix known typo route to a safe fallback
            'bancassurance.medfund.requests.index' => null,
        ];

        foreach ($updates as $from => $to) {
            if ($to === null) {
                DB::table('t_Modules')->where('Route', $from)->update(['Route' => null]);
            } else {
                DB::table('t_Modules')->where('Route', $from)->update(['Route' => $to]);
            }
        }
    }
}
