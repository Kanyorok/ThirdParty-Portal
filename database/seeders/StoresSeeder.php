<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory\Store;
use Carbon\Carbon;

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
                'StoreID'     => 'ST001',
                'StoreName'   => 'Procurement Store',
                'BranchID'    => 1,
                'Status'      => 'Active',
                'CreatedBy'     => 1,
                'ModifiedBy'    => 1,
                'DeletedBy'     => null,
                'CreatedOn'    => Carbon::now(),
                'ModifiedOn'    => Carbon::now(),
            ],
        ]);  
    } 
}
}