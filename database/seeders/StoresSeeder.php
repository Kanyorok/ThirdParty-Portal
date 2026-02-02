<?php

namespace Database\Seeders;

use App\Models\Inventory\Store;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        {

            Store::insert([
                [
                    'StoreID' => 'ST001',
                    'StoreName' => 'Procurement Store',
                    'BranchID' => 1,
                    'Status' => 'Active',
                    'CreatedBy' => 1,
                    'ModifiedBy' => 1,
                    'DeletedBy' => null,
                    'CreatedOn' => Carbon::now(),
                    'ModifiedOn' => Carbon::now(),
                ],
            ]);
        }
    }
}
