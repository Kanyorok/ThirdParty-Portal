<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicalFundsCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Seed t_Coverages
        if (Schema::hasTable('t_Coverages')) {
            $coverages = [
                ['Code' => 'INPATIENT',  'Name' => 'Inpatient',  'Description' => 'Hospital admission cover'],
                ['Code' => 'OUTPATIENT', 'Name' => 'Outpatient', 'Description' => 'Clinic visits & GP'],
                ['Code' => 'DENTAL',     'Name' => 'Dental',     'Description' => 'Dental procedures'],
                ['Code' => 'OPTICAL',    'Name' => 'Optical',    'Description' => 'Eye tests & lenses'],
                ['Code' => 'MATERNITY',  'Name' => 'Maternity',  'Description' => 'Maternity care'],
            ];

            foreach ($coverages as $c) {
                $exists = DB::table('t_Coverages')->where('Code', $c['Code'])->exists();
                if (! $exists) {
                    DB::table('t_Coverages')->insert([
                        'Code' => $c['Code'],
                        'Name' => $c['Name'],
                        'Description' => $c['Description'],
                        'IsActive' => 1,
                    ]);
                }
            }
        }

        // Seed t_BeneficiaryRelationships
        if (Schema::hasTable('t_BeneficiaryRelationships')) {
            $rels = [
                ['Code' => 'SELF',    'Name' => 'Self'],
                ['Code' => 'SPOUSE',  'Name' => 'Spouse'],
                ['Code' => 'CHILD',   'Name' => 'Child'],
                ['Code' => 'PARENT',  'Name' => 'Parent'],
                ['Code' => 'GUARDIAN','Name' => 'Guardian'],
                ['Code' => 'SIBLING', 'Name' => 'Sibling'],
                ['Code' => 'OTHER',   'Name' => 'Other'],
            ];

            foreach ($rels as $r) {
                $exists = DB::table('t_BeneficiaryRelationships')->where('Code', $r['Code'])->exists();
                if (! $exists) {
                    DB::table('t_BeneficiaryRelationships')->insert([
                        'Code' => $r['Code'],
                        'Name' => $r['Name'],
                        'IsActive' => 1,
                    ]);
                }
            }
        }
    }
}
