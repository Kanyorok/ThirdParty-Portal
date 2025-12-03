<?php

namespace Database\Seeders;

use App\Models\Core\CategoryMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;



class CategoryMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'Name' => 'warehouse',
                'Description' => 'Warehouse category',
                'Type' => 'PropertyCategory',
                'Code' => '500000',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
            ],
            [
                'Name' => 'Commercial',
                'Description' => 'Commercial category',
                'Type' => 'PropertyCategory',
                'Code' => '500000',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
            ],
            [
                'Name' => 'Residential',
                'Description' => 'Residential category',
                'Type' => 'PropertyCategory',
                'Code' => '500000',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
            ],
            [
                'Name' => 'Tenant',
                'Description' => 'Tenant category',
                'Type' => 'TenantCategory',
                'Code' => '700000',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
            ],
            [
                'Name' => 'Supplier',
                'Description' => 'Supplier category',
                'Type' => 'SupplierCategory',
                'Code' => '600000',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
            ],
            [
                'Name' => 'Customer',
                'Description' => 'Customer category',
                'Type' => 'CustomerCategory',
                'Code' => '800000',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
            ],
        ];

        foreach ($categories as $data) {
            $existing = CategoryMaster::where('Name', $data['Name'])
                ->where('Type', $data['Type'])
                ->first();

            if ($existing) {
                // Update only mutable fields & modified audit; keep original CreatedOn/CreatedBy
                $existing->Description = $data['Description'];
                $existing->Code = $data['Code'];
                $existing->ModifiedBy = $data['ModifiedBy'];
                $existing->ModifiedOn = $now;
                $existing->save();
            } else {
                CategoryMaster::create([
                    'Name' => $data['Name'],
                    'Description' => $data['Description'],
                    'Type' => $data['Type'],
                    'Code' => $data['Code'],
                    'CreatedBy' => $data['CreatedBy'],
                    'ModifiedBy' => $data['ModifiedBy'],
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                ]);
            }
        }

        // Optional duplicate cleanup: keep earliest per (Name,Type) if legacy duplicates exist
        $duplicates = CategoryMaster::select('Name', 'Type')
            ->groupBy('Name', 'Type')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($duplicates as $dup) {
            $toKeep = CategoryMaster::where('Name', $dup->Name)->where('Type', $dup->Type)
                ->orderBy('CreatedOn', 'asc')->first();
            CategoryMaster::where('Name', $dup->Name)->where('Type', $dup->Type)
                ->where('Id', '!=', $toKeep->Id ?? 0)->delete();
        }
    }
}
