<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatutorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $actor = 1;

        // SHA/SHIF: single percentage with minimum
        DB::table('t_HRStatutoryNHIFRates')->updateOrInsert(
            ['BandName' => 'SHA/SHIF'],
            [
                'BandName' => 'SHA/SHIF',
                'IncomeFrom' => 0,
                'IncomeTo' => null,
                'EmployeeRate' => 2.75,
                'EmployerRate' => 0,
                'MinAmount' => 300,
                'IsPercentage' => true,
                'IsActive' => 1,
                'EffectiveFrom' => '2025-01-01',
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );

        $nssf = [
            ['Tier' => 'Tier I', 'IncomeFrom' => 0, 'IncomeTo' => 7000, 'EmployeeRate' => 0.06, 'EmployerRate' => 0.06, 'IsPercentage' => true],
            ['Tier' => 'Tier II', 'IncomeFrom' => 7000, 'IncomeTo' => 36000, 'EmployeeRate' => 0.06, 'EmployerRate' => 0.06, 'IsPercentage' => true],
        ];
        foreach ($nssf as $row) {
            DB::table('t_HRStatutoryNSSFRates')->updateOrInsert(
                ['Tier' => $row['Tier']],
                array_merge($row, [
                    'IsActive' => 1,
                    'EffectiveFrom' => '2025-01-01',
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        $paye = [
            ['LowerLimit' => 0, 'UpperLimit' => 24000, 'Rate' => 10, 'FixedAmount' => 0],
            ['LowerLimit' => 24000, 'UpperLimit' => 32333, 'Rate' => 25, 'FixedAmount' => 0],
            ['LowerLimit' => 32333, 'UpperLimit' => null, 'Rate' => 30, 'FixedAmount' => 0],
        ];
        foreach ($paye as $row) {
            DB::table('t_HRStatutoryPAYEBands')->updateOrInsert(
                ['LowerLimit' => $row['LowerLimit'], 'UpperLimit' => $row['UpperLimit']],
                array_merge($row, [
                    'IsActive' => 1,
                    'EffectiveFrom' => '2025-01-01',
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        DB::table('t_HRStatutoryHousingLevyRates')->updateOrInsert(
            ['EffectiveFrom' => '2025-01-01'],
            [
                'Rate' => 1.5,
                'CapAmount' => null,
                'Description' => 'Default housing levy rate',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );

        $fringe = [
            ['Code' => 'CAR', 'Name' => 'Company Car Benefit', 'RateType' => 'Percentage', 'Rate' => 2.0, 'CapAmount' => null],
            ['Code' => 'LOAN', 'Name' => 'Low Interest Loan', 'RateType' => 'Percentage', 'Rate' => 1.0, 'CapAmount' => null],
        ];
        foreach ($fringe as $row) {
            DB::table('t_HRStatutoryFringeBenefits')->updateOrInsert(
                ['Code' => $row['Code']],
                array_merge($row, [
                    'IsActive' => 1,
                    'EffectiveFrom' => '2025-01-01',
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        // Payroll deductions (dynamic) with SHIF/SHA configuration
        $shifId = DB::table('t_HRPayrollDeductions')->updateOrInsert(
            ['Code' => 'SHIF'],
            [
                'Name' => 'SHA/SHIF',
                'Description' => 'Social Health Authority deduction',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );
        $shifDeductionId = DB::table('t_HRPayrollDeductions')->where('Code', 'SHIF')->value('Id');
        if ($shifDeductionId) {
            DB::table('t_HRPayrollDeductionRules')->updateOrInsert(
                [
                    'DeductionID' => $shifDeductionId,
                    'EffectiveFrom' => '2025-01-01',
                ],
                [
                    'CalcMethod' => 'PercentageOnGross',
                    'Rate' => 2.75,
                    'Amount' => null,
                    'IncomeFrom' => 0,
                    'IncomeTo' => null,
                    'MinAmount' => 300,
                    'MaxAmount' => null,
                    'HasRelief' => 0,
                    'ReliefType' => null,
                    'ReliefRate' => null,
                    'ReliefAmount' => null,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ]
            );
        }

        $reliefs = [
            [
                'Code' => 'PERS',
                'Name' => 'Personal Relief',
                'ReliefType' => 'Fixed',
                'ReliefRate' => null,
                'DeductionID' => null,
                'Amount' => 2400,
                'ApplyStage' => 'PostTax',
            ],
            [
                'Code' => 'INS',
                'Name' => 'Insurance Relief',
                'ReliefType' => 'Fixed',
                'ReliefRate' => null,
                'DeductionID' => null,
                'Amount' => 5000,
                'ApplyStage' => 'PostTax',
            ],
        ];
        foreach ($reliefs as $row) {
            DB::table('t_HRStatutoryReliefs')->updateOrInsert(
                ['Code' => $row['Code']],
                array_merge($row, [
                    'IsActive' => 1,
                    'EffectiveFrom' => '2025-01-01',
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }

        DB::table('t_HRGratuitySettings')->updateOrInsert(
            ['ApplyFor' => 'Contract'],
            [
                'RatePercent' => 5,
                'CalcBasis' => 'Basic',
                'EffectiveFrom' => '2025-01-01',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );


        // Acting Allowance default (percentage on basic)
        DB::table('t_HRPayrollAllowances')->updateOrInsert(
            ['Code' => 'ACTING'],
            [
                'Name' => 'Acting Allowance',
                'Description' => 'Allowance for acting assignments',
                'IsTaxable' => 1,
                // Not mandatory for all employees; applied only when an acting assignment exists.
                'IsMandatory' => 0,
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );
        $actingId = DB::table('t_HRPayrollAllowances')->where('Code','ACTING')->value('Id');
        if ($actingId) {
            DB::table('t_HRPayrollAllowanceRules')->updateOrInsert(
                ['AllowanceID' => $actingId, 'EffectiveFrom' => '2025-01-01'],
                [
                    'CalcMethod' => 'PercentageOfActingReference',
                    'Rate' => 20,
                    'Amount' => null,
                    'IncomeFrom' => 0,
                    'IncomeTo' => null,
                    'MinAmount' => null,
                    'MaxAmount' => null,
                    'FormulaText' => null,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ]
            );
        }        // Default staff loan repayment (repayments are staged from loan schedules into payroll deductions)
        DB::table('t_HRPayrollDeductions')->updateOrInsert(
            ['Code' => 'LOAN-REP'],
            [
                'Name' => 'Staff Loan Repayment',
                'Description' => 'Repayments pushed from staff loans into payroll',
                // Not mandatory for all employees; created only when a staff loan is approved.
                'IsMandatory' => 0,
                'ShowInPayslip' => 1,
                'ApplyFor' => 'All',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );

        // PAYE deduction scaffold using taxable income + post-tax reliefs
        $payeId = DB::table('t_HRPayrollDeductions')->updateOrInsert(
            ['Code' => 'PAYE'],
            [
                'Name' => 'PAYE',
                'Description' => 'Pay As You Earn',
                'IsMandatory' => 1,
                'ShowInPayslip' => 1,
                'ApplyFor' => 'All',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor,
                'ModifiedOn' => $now,
            ]
        );
        $payeDedId = DB::table('t_HRPayrollDeductions')->where('Code','PAYE')->value('Id');
        if ($payeDedId) {
            DB::table('t_HRPayrollDeductionRules')->updateOrInsert(
                [
                    'DeductionID' => $payeDedId,
                    'EffectiveFrom' => '2025-01-01',
                    'IncomeFrom' => 0,
                    'IncomeTo' => null,
                ],
                [
                    'CalcMethod' => 'PAYEOnTaxableIncome',
                    'Rate' => 0,
                    'Amount' => null,
                    'MinAmount' => null,
                    'MaxAmount' => null,
                    'HasRelief' => 0,
                    'AffectsTaxableIncome' => 0,
                    'IsTaxRelief' => 0,
                    'ReliefType' => null,
                    'ReliefAmount' => null,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ]
            );

        }

    }
}
