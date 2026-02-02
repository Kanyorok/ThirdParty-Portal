<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimplePOTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Seeding Simple PO Test Data...');

        // 1. Update existing awards to have proper contract statuses
        $this->updateExistingAwards();

        // 2. Create additional awards for testing
        $this->createAdditionalAwards();

        // 3. Update procurement plan items to have 'Direct' method
        $this->updateProcurementPlans();

        // 4. Create additional tender items for existing tenders
        $this->createTenderItems();

        // 5. Verify the data
        $this->verifyData();

        $this->command->info('✅ Simple PO Test Data seeding completed!');
    }

    private function updateExistingAwards()
    {
        $this->command->info("🔄 Updating existing awards...");

        // Update the existing award to have better data for testing
        $updated = DB::table('t_TenderAwards')
            ->where('Id', 1)
            ->update([
                'ContractStatus' => 'Active',
                'ContractRef' => 'CONT-ACTIVE-001',
                'ContractValue' => 1500000.00,
                'ContractStartDate' => now()->subDays(10),
                'ContractEndDate' => now()->addDays(355),
                'PaymentTerms' => 'Net 30 days upon delivery and acceptance',
                'DeliveryTerms' => 'Free delivery to client premises within Nairobi',
                'SpecialConditions' => '12 months warranty, performance bond required',
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]);

        if ($updated) {
            $this->command->info("  ✅ Updated award ID 1 to Active contract status");
        }
    }

    private function createAdditionalAwards()
    {
        $this->command->info("🏆 Creating additional awards...");

        // Get existing tenders and suppliers
        $tenders = DB::table('t_Tenders')->where('Id', '>', 1)->take(2)->get();
        $suppliers = DB::table('t_Suppliers')->take(3)->get();

        $supplierIndex = 0;
        foreach ($tenders as $tender) {
            $existingAward = DB::table('t_TenderAwards')->where('TenderID', $tender->Id)->exists();

            if (! $existingAward && isset($suppliers[$supplierIndex])) {
                $supplier = $suppliers[$supplierIndex];

                // Create award with different contract statuses for testing
                $contractStatuses = ['Draft', 'Active', 'Approved'];
                $contractStatus = $contractStatuses[$supplierIndex % 3];

                $awardData = [
                    'TenderID' => $tender->Id,
                    'WinningSupplierID' => $supplier->Id,
                    'AwardedAmount' => rand(500000, 2000000),
                    'AwardStatus' => 'Approved',
                    'AwardDate' => now()->subDays(15 + $supplierIndex * 5),
                    'ContractStartDate' => now()->addDays(5 + $supplierIndex * 10),
                    'ContractEndDate' => now()->addDays(365 + $supplierIndex * 30),
                    'AwardJustification' => 'Best evaluated bid with competitive pricing and technical compliance',
                    'TechnicalScore' => rand(80, 95),
                    'FinancialScore' => rand(85, 98),
                    'TotalScore' => rand(83, 96),
                    'ContractStatus' => $contractStatus,
                    'ContractRef' => 'CONT-' . strtoupper(substr(md5($tender->Id . $supplier->Id), 0, 8)),
                    'ContractValue' => rand(500000, 2000000),
                    'PaymentTerms' => 'Net 30 days',
                    'DeliveryTerms' => 'FOB destination',
                    'SpecialConditions' => 'Standard terms and conditions apply',
                    'CreatedBy' => 1,
                    'ModifiedBy' => 1,
                    'CreatedOn' => now()->subDays(20 + $supplierIndex * 5),
                    'ModifiedOn' => now()->subDays(15 + $supplierIndex * 5),
                ];

                DB::table('t_TenderAwards')->insert($awardData);
                $this->command->info("  ✅ Created award for tender {$tender->TenderNo} (Status: {$contractStatus})");

                $supplierIndex++;
            }
        }
    }

    private function updateProcurementPlans()
    {
        $this->command->info("📋 Updating procurement plan items...");

        // Update existing plan items to have 'Direct' procurement method
        $updated = DB::table('t_PlanLineItem')
            ->where('ProcurementMethod', '107') // Update the existing numeric method
            ->orWhereNull('ProcurementMethod')
            ->update([
                'ProcurementMethod' => 'Direct',
                'ExecutionStatus' => 'Approved',
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]);

        if ($updated > 0) {
            $this->command->info("  ✅ Updated {$updated} plan items to use 'Direct' procurement method");
        }

        // Create additional plan line items for testing
        $plans = DB::table('t_ConsolidatedProcurementPlan')->take(1)->get();

        foreach ($plans as $plan) {
            $existingItems = DB::table('t_PlanLineItem')
                ->where('PlanID', $plan->PlanID)
                ->count();

            if ($existingItems < 3) {
                $newItems = [
                    [
                        'PlanID' => $plan->PlanID,
                        'CategoryID' => 1,
                        'BranchID' => 1,
                        'DepartmentID' => 1,
                        'MergedQty' => 15,
                        'UnitOfMeasure' => 'Units',
                        'EstimatedUnitCost' => 45000,
                        'ProcurementMethod' => 'Direct',
                        'SchedulePeriod' => 'Q1 2025',
                        'ExecutionStatus' => 'Approved',
                        'ExpectedDeliveryDate' => now()->addDays(20),
                        'SourceType' => 'Budget',
                        'OriginalQTY' => 15,
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(40),
                        'ModifiedOn' => now()->subDays(40),
                    ],
                    [
                        'PlanID' => $plan->PlanID,
                        'CategoryID' => 2,
                        'BranchID' => 1,
                        'DepartmentID' => 1,
                        'MergedQty' => 8,
                        'UnitOfMeasure' => 'Sets',
                        'EstimatedUnitCost' => 85000,
                        'ProcurementMethod' => 'Direct',
                        'SchedulePeriod' => 'Q2 2025',
                        'ExecutionStatus' => 'Approved',
                        'ExpectedDeliveryDate' => now()->addDays(40),
                        'SourceType' => 'Budget',
                        'OriginalQTY' => 8,
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(35),
                        'ModifiedOn' => now()->subDays(35),
                    ],
                ];

                foreach ($newItems as $item) {
                    DB::table('t_PlanLineItem')->insert($item);
                }

                $this->command->info("  ✅ Added line items to plan {$plan->PlanID}");
            }
        }
    }

    private function createTenderItems()
    {
        $this->command->info("📦 Creating tender items...");

        // Get all tenders and create items for those that don't have enough
        $tenders = DB::table('t_Tenders')->get();

        $itemTemplates = [
            ['name' => 'Laptop Computers', 'desc' => 'Business grade laptops with 3-year warranty', 'qty' => 12],
            ['name' => 'Office Desks', 'desc' => 'Executive office desks with drawers', 'qty' => 8],
            ['name' => 'Printer Cartridges', 'desc' => 'Original ink cartridges for office printers', 'qty' => 50],
            ['name' => 'Meeting Room Tables', 'desc' => 'Conference tables for 8-10 people', 'qty' => 4],
            ['name' => 'Filing Cabinets', 'desc' => 'Metal filing cabinets with locks', 'qty' => 10],
        ];

        foreach ($tenders as $tender) {
            $existingItems = DB::table('t_TenderItems')->where('TenderID', $tender->Id)->count();

            if ($existingItems < 2) {
                // Add 2-3 items per tender
                $itemsToAdd = array_slice($itemTemplates, 0, rand(2, 3));

                foreach ($itemsToAdd as $item) {
                    DB::table('t_TenderItems')->insert([
                        'TenderID' => $tender->Id,
                        'SourceType' => 'Manual',
                        'ManualItemDescription' => $item['name'],
                        'QtyToTender' => $item['qty'],
                        'Remarks' => $item['desc'],
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(30),
                        'ModifiedOn' => now()->subDays(30),
                    ]);
                }

                $this->command->info("  ✅ Added items to tender {$tender->TenderNo}");
            }
        }
    }

    private function verifyData()
    {
        $this->command->info("🔍 Verifying data for PO form...");

        // Check awards
        $approvedAwards = DB::table('t_TenderAwards')
            ->where('AwardStatus', 'Approved')
            ->count();
        $this->command->info("  📊 Approved awards: {$approvedAwards}");

        $activeContracts = DB::table('t_TenderAwards')
            ->where('AwardStatus', 'Approved')
            ->where('ContractStatus', 'Active')
            ->count();
        $this->command->info("  📊 Active contracts: {$activeContracts}");

        // Check procurement plans
        $directPlanItems = DB::table('t_PlanLineItem')
            ->join('t_ConsolidatedProcurementPlan', 't_PlanLineItem.PlanID', '=', 't_ConsolidatedProcurementPlan.PlanID')
            ->where('t_ConsolidatedProcurementPlan.Status', 'Approved')
            ->where('t_PlanLineItem.ProcurementMethod', 'Direct')
            ->where('t_PlanLineItem.ExecutionStatus', '!=', 'Completed')
            ->count();
        $this->command->info("  📊 Direct procurement plan items: {$directPlanItems}");

        // Check tender items
        $totalTenderItems = DB::table('t_TenderItems')->count();
        $this->command->info("  📊 Total tender items: {$totalTenderItems}");

        if ($approvedAwards > 0 && $activeContracts > 0 && $directPlanItems > 0) {
            $this->command->info("✅ All data types are available for testing!");
        } else {
            $this->command->warn("⚠️  Some data types may not be available for testing");
        }
    }
}
