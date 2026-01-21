<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierApprovalMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $dated = now()->toDateTimeString();

        $modules = [
            ['ModuleID' => 303300, 'Name' => 'Supplier Approvals', 'Icon' => null, 'Description' => 'Approve pending suppliers', 'ParentID' => 303000, 'Route' => 'suppliers-approval.index'],
        ];

        foreach ($modules as $module) {
            $existing = DB::table('t_Modules')
                ->where('ModuleID', $module['ModuleID'])
                ->first();

            if ($existing) {
                // Update existing
                DB::table('t_Modules')
                    ->where('ModuleID', $module['ModuleID'])
                    ->update([
                        'Name' => $module['Name'],
                        'Icon' => $module['Icon'],
                        'Description' => $module['Description'],
                        'Route' => $module['Route'],
                        'ParentID' => $module['ParentID'],
                        'ModifiedBy' => $actor->Id,
                        'ModifiedOn' => $dated,
                    ]);
            } else {
                // Insert new
                DB::table('t_Modules')->insert([
                    'ModuleID' => $module['ModuleID'],
                    'Name' => $module['Name'],
                    'Icon' => $module['Icon'],
                    'Description' => $module['Description'],
                    'Route' => $module['Route'],
                    'ParentID' => $module['ParentID'],
                    'CreatedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedBy' => $actor->Id,
                    'ModifiedOn' => $dated,
                ]);
            }
        }

        $this->command->info('Supplier Approval menu item seeded successfully.');
    }
}
