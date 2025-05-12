<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Supplier;

class RFQSupplierSeeder extends Seeder
{
    public function run(): void
    {
        $rfqs = RFQ::all();
        $suppliers = Supplier::all();

        foreach ($rfqs as $rfq) {
            $rfq->suppliers()->attach(
                $suppliers->random(rand(1, 3))->pluck('Id')->toArray(),
                ['Status' => 'Pending']
            );
        }
    }
}