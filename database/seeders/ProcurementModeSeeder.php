<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementModeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_ProcurementModes')->insert([
            [
                'Name' => 'Open Tender',
                'Description' => 'A competitive bidding process open to all qualified suppliers.',
                'UniqueCode' => 'OT-001',
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Name' => 'Restricted Tender',
                'Description' => 'A bidding process limited to pre-qualified suppliers.',
                'UniqueCode' => 'RT-002',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Name' => 'Direct Procurement',
                'Description' => 'Procurement from a single supplier without competition.',
                'UniqueCode' => 'DP-003',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
