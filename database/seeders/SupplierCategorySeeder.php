<?php

namespace Database\Seeders;

use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Database\Seeder;

class SupplierCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // SupplierCategory::truncate(); // Cannot truncate due to FK constraints

        $cats = [
            [
                'CategoryName' => 'Construction and Works',
                'Description' => 'Suppliers providing construction, renovation, and civil engineering services.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'Non-Consultancy Services',
                'Description' => 'Suppliers offering general services such as cleaning, maintenance, and security.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'Consultancy Services',
                'Description' => 'Suppliers providing expert advice, professional services, and specialized reports.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'Supply of Goods',
                'Description' => 'Suppliers providing physical products, materials, and finished goods.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'IT Services',
                'Description' => 'Providers of hardware, software, and technical support solutions.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'Office Supplies',
                'Description' => 'Suppliers of general office stationery, furniture, and equipment.',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
            [
                'CategoryName' => 'Other',
                'Description' => 'other',
                'IsActive' => true,
                'CreatedBy' => 2,
                'ModifiedBy' => null,
            ],
        ];

        foreach ($cats as $cat) {
            SupplierCategory::firstOrCreate(
                ['CategoryName' => $cat['CategoryName']],
                $cat
            );
        }
    }
}
