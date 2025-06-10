<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\UnitOfMeasure;
use Carbon\Carbon;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $uoms = [
            [
                'Code' => 'PCS',
                'Name' => 'Piece',
                'BaseUnit' => true,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'Code' => 'KG',
                'Name' => 'Kilogram',
                'BaseUnit' => true,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'Code' => 'LTR',
                'Name' => 'Liter',
                'BaseUnit' => true,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'Code' => 'BOX',
                'Name' => 'Box',
                'BaseUnit' => false,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'Code' => 'REAM',
                'Name' => 'Ream',
                'BaseUnit' => false,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'Code' => 'PACK',
                'Name' => 'Pack',
                'BaseUnit' => false,
                'Active' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
        ];

        foreach ($uoms as $uom) {
            UnitOfMeasure::updateOrCreate(
                ['Code' => $uom['Code']],
                $uom
            );
        }
    }
}
