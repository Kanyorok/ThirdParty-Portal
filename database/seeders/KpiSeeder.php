<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $actor = 1;

        $categories = [
            ['Code' => 'FIN', 'Name' => 'Financial', 'Description' => 'Financial KPIs'],
            ['Code' => 'CUST', 'Name' => 'Customer', 'Description' => 'Customer experience KPIs'],
            ['Code' => 'OPS', 'Name' => 'Operations', 'Description' => 'Process/operations KPIs'],
            ['Code' => 'PEOPLE', 'Name' => 'People', 'Description' => 'HR / people metrics'],
        ];

        foreach ($categories as $cat) {
            DB::table('t_HRKPICategories')->updateOrInsert(
                ['Code' => $cat['Code']],
                array_merge($cat, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $units = [
            ['Code' => 'PCT', 'Name' => 'Percent', 'Description' => 'Percentage %'],
            ['Code' => 'COUNT', 'Name' => 'Count', 'Description' => 'Raw count'],
            ['Code' => 'SCORE', 'Name' => 'Score', 'Description' => 'Numeric score'],
            ['Code' => 'DAYS', 'Name' => 'Days', 'Description' => 'Number of days'],
        ];

        foreach ($units as $unit) {
            DB::table('t_HRKPIUnits')->updateOrInsert(
                ['Code' => $unit['Code']],
                array_merge($unit, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $kpis = [
            ['Code' => 'REV_GROWTH', 'Name' => 'Revenue Growth', 'Category' => 'Financial', 'Unit' => '%', 'DefaultWeight' => 20, 'Description' => 'Year over year revenue growth'],
            ['Code' => 'CSAT', 'Name' => 'Customer Satisfaction', 'Category' => 'Customer', 'Unit' => 'Score', 'DefaultWeight' => 15, 'Description' => 'Average satisfaction score'],
            ['Code' => 'OTIF', 'Name' => 'On-time Delivery', 'Category' => 'Operations', 'Unit' => '%', 'DefaultWeight' => 10, 'Description' => 'Deliveries on time and in full'],
        ];

        foreach ($kpis as $kpi) {
            $categoryId = DB::table('t_HRKPICategories')->where('Name', $kpi['Category'])->value('Id');
            $unitId = DB::table('t_HRKPIUnits')->where('Name', $kpi['Unit'])->value('Id');
            DB::table('t_HRKPIItems')->updateOrInsert(
                ['Code' => $kpi['Code']],
                array_merge($kpi, [
                    'CategoryID' => $categoryId,
                    'UnitID' => $unitId,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $scales = [
            ['Code' => 'STD_5PT', 'Name' => 'Standard 5-Point', 'MinScore' => 1, 'MaxScore' => 5, 'Description' => '1-5 scale'],
            ['Code' => 'STD_10PT', 'Name' => 'Standard 10-Point', 'MinScore' => 1, 'MaxScore' => 10, 'Description' => '1-10 scale'],
        ];

        foreach ($scales as $scale) {
            DB::table('t_HRKPIRatingScales')->updateOrInsert(
                ['Code' => $scale['Code']],
                array_merge($scale, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $periods = [
            ['Code' => 'QTR', 'Name' => 'Quarterly', 'StartMonth' => 1, 'EndMonth' => 3, 'Description' => 'Quarterly KPI period'],
            ['Code' => 'H1', 'Name' => 'Half-Year', 'StartMonth' => 1, 'EndMonth' => 6, 'Description' => 'First half'],
            ['Code' => 'FY', 'Name' => 'Full Year', 'StartMonth' => 1, 'EndMonth' => 12, 'Description' => 'Full financial year'],
        ];

        foreach ($periods as $period) {
            DB::table('t_HRKPIPeriods')->updateOrInsert(
                ['Code' => $period['Code']],
                array_merge($period, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $formulas = [
            ['Code' => 'PCT_ACH', 'Name' => 'Percent Achievement', 'Expression' => '(Actual/Target)*100', 'Description' => 'Simple percent achievement'],
            ['Code' => 'CSAT_SCORE', 'Name' => 'CSAT Weighted', 'Expression' => '((Promoters - Detractors)/Total)*100', 'Description' => 'Net promoter style scoring'],
        ];

        foreach ($formulas as $formula) {
            DB::table('t_HRKPIFormulas')->updateOrInsert(
                ['Code' => $formula['Code']],
                array_merge($formula, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }
    }
}
