<?php

namespace Database\Seeders;

use App\Models\Core\CategoryMaster;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CategoryMasterSeeder extends Seeder
{
    public function run(): void
    {
        CategoryMaster::create([
            'Name' => 'warehouse',
            'Description' => 'Warehouse category',
            'Type' => 'PropertyCategory',
            'Code' => '500000',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => Carbon::now(),
            'ModifiedOn' => Carbon::now(),
        ]);

        CategoryMaster::create([
            'Name' => 'Commercial',
            'Description' => 'Commercial category',
            'Type' => 'PropertyCategory',
            'Code' => '500000',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => Carbon::now(),
            'ModifiedOn' => Carbon::now(),
        ]);

        CategoryMaster::create([
            'Name' => 'Residential',
            'Description' => 'Residential category',
            'Type' => 'PropertyCategory',
            'Code' => '500000',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => Carbon::now(),
            'ModifiedOn' => Carbon::now(),
        ]);
    }
}
