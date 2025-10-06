<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnifiedPOTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Seeding Unified PO Test Data...');
        
        // 1. Create additional Tenders if needed
        $this->seedTenders();
        
        // 2. Create additional Suppliers if needed  
        $this->seedSuppliers();
        
        // 3. Create TenderItems for existing tenders
        $this->seedTenderItems();
        
        // 4. Create more TenderAwards with proper statuses
        $this->seedTenderAwards();
        
        // 5. Create ConsolidatedProcurementPlan if needed
        $this->seedProcurementPlans();
        
        // 6. Create PlanLineItems with correct ProcurementMethod
        $this->seedPlanLineItems();
        
        // 7. Update existing data to be compatible
        $this->updateExistingData();
        
        $this->command->info('✅ Unified PO Test Data seeding completed!');
    }
    
    private function seedTenders()
    {
        $existingTenders = DB::table('t_Tenders')->count();
        $this->command->info("📋 Found {$existingTenders} existing tenders");
        
        // Add more tenders for testing if needed
        $additionalTenders = [
            [
                'TenderNo' => 'TNDR-UNIFIED-001',
                'Title' => 'Office Supplies and Equipment Procurement',
                'TenderType' => 'op', // Based on existing data format
                'TenderCategory' => 1, // Required field
                'ScopeOfWork' => 'Supply of office equipment, stationery, and IT accessories',
                'Instructions' => 'Standard procurement procedures apply',
                'Status' => 'cl', // Closed, following existing pattern
                'SubmissionDeadline' => now()->subDays(30),
                'OpeningDate' => now()->subDays(25),
                'CurrencyId' => 56, // Same as existing
                'ItemCategoryId' => 1, // Required field
                'ApprovalStatus' => 1, // Approved
                'SubmissionModel' => 'Single',
                'BidValidityDays' => 30,
                'BidSecurityAmountType' => 'Percentage',
                'BidSecurityAmount' => 0.00,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now()->subDays(45),
                'ModifiedOn' => now()->subDays(25),
            ],
            [
                'TenderNo' => 'TNDR-UNIFIED-002',  
                'Title' => 'Facility Maintenance Services Contract',
                'TenderType' => 'rs', // Restricted
                'TenderCategory' => 1,
                'ScopeOfWork' => 'Comprehensive facility maintenance and cleaning services',
                'Instructions' => 'Service-based procurement with performance guarantees',
                'Status' => 'cl',
                'SubmissionDeadline' => now()->subDays(20),
                'OpeningDate' => now()->subDays(15),
                'CurrencyId' => 56,
                'ItemCategoryId' => 1,
                'ApprovalStatus' => 1,
                'SubmissionModel' => 'Single',
                'BidValidityDays' => 30,
                'BidSecurityAmountType' => 'Percentage',
                'BidSecurityAmount' => 0.00,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now()->subDays(35),
                'ModifiedOn' => now()->subDays(15),
            ]
        ];
        
        foreach ($additionalTenders as $tender) {
            $exists = DB::table('t_Tenders')->where('TenderNo', $tender['TenderNo'])->exists();
            if (!$exists) {
                DB::table('t_Tenders')->insert($tender);
                $this->command->info("  ✅ Created tender: {$tender['TenderNo']}");
            }
        }
    }
    
    private function seedSuppliers()
    {
        $existingSuppliers = DB::table('t_Suppliers')->count();
        $this->command->info("🏢 Found {$existingSuppliers} existing suppliers");
        
        // Add more suppliers if we have less than 5
        if ($existingSuppliers < 5) {
            $additionalSuppliers = [
                [
                    'TradingName' => 'Alpha Office Solutions Ltd',
                    'ThirdPartyName' => 'Alpha Office Solutions Ltd',
                    'Address' => '123 Business Park, Nairobi',
                    'Phone' => '+254-700-123456',
                    'Email' => 'info@alphaoffice.co.ke',
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'ModifiedBy' => 1,
                    'CreatedOn' => now()->subDays(60),
                    'ModifiedOn' => now()->subDays(60),
                ],
                [
                    'TradingName' => 'Beta Facility Services',
                    'ThirdPartyName' => 'Beta Facility Services',
                    'Address' => '456 Industrial Area, Mombasa',
                    'Phone' => '+254-700-654321', 
                    'Email' => 'contact@betafacility.com',
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'ModifiedBy' => 1,
                    'CreatedOn' => now()->subDays(50),
                    'ModifiedOn' => now()->subDays(50),
                ],
                [
                    'TradingName' => 'Gamma Tech Supplies',
                    'ThirdPartyName' => 'Gamma Tech Supplies',
                    'Address' => '789 Tech Hub, Kisumu',
                    'Phone' => '+254-700-987654',
                    'Email' => 'sales@gammatech.co.ke', 
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'ModifiedBy' => 1,
                    'CreatedOn' => now()->subDays(40),
                    'ModifiedOn' => now()->subDays(40),
                ]
            ];
            
            foreach ($additionalSuppliers as $supplier) {
                $exists = DB::table('t_Suppliers')->where('TradingName', $supplier['TradingName'])->exists();
                if (!$exists) {
                    DB::table('t_Suppliers')->insert($supplier);
                    $this->command->info("  ✅ Created supplier: {$supplier['TradingName']}");
                }
            }
        }
    }
    
    private function seedTenderItems()
    {
        $this->command->info("📦 Seeding tender items...");
        
        // Get tenders that need items
        $tenders = DB::table('t_Tenders')->get();
        
        // Sample items data
        $sampleItems = [
            ['name' => 'Desktop Computers', 'description' => 'Dell OptiPlex 7090 or equivalent', 'qty' => 10],
            ['name' => 'Laser Printers', 'description' => 'HP LaserJet Pro series', 'qty' => 3],
            ['name' => 'Office Chairs', 'description' => 'Ergonomic office chairs with lumbar support', 'qty' => 15],
            ['name' => 'Cleaning Services', 'description' => 'Daily office cleaning and maintenance', 'qty' => 12], // months
            ['name' => 'Security Services', 'description' => '24/7 security guard services', 'qty' => 12], // months
            ['name' => 'Stationery Package', 'description' => 'Complete office stationery supplies', 'qty' => 50]
        ];
        
        foreach ($tenders as $tender) {
            $existingItems = DB::table('t_TenderItems')->where('TenderID', $tender->Id)->count();
            
            if ($existingItems < 2) {
                // Add 2-3 items per tender
                $itemsToAdd = array_slice($sampleItems, 0, rand(2, 3));
                
                foreach ($itemsToAdd as $item) {
                    DB::table('t_TenderItems')->insert([
                        'TenderID' => $tender->Id,
                        'SourceType' => 'Manual',
                        'ManualItemDescription' => $item['name'],
                        'QtyToTender' => $item['qty'],
                        'Remarks' => $item['description'],
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now(),
                        'ModifiedOn' => now(),
                    ]);
                }
                
                $this->command->info("  ✅ Added items to tender: {$tender->TenderNo}");
            }
        }
    }
    
    private function seedTenderAwards()
    {
        $this->command->info("🏆 Seeding tender awards...");
        
        // Get available tenders and suppliers
        $tenders = DB::table('t_Tenders')->limit(3)->get();
        $suppliers = DB::table('t_Suppliers')->limit(3)->get();
        
        $awardIndex = 0;
        foreach ($tenders as $tender) {
            $existingAward = DB::table('t_TenderAwards')->where('TenderID', $tender->Id)->exists();
            
            if (!$existingAward && isset($suppliers[$awardIndex])) {
                $supplier = $suppliers[$awardIndex];
                $awardIndex++;
                
                // Create different types of awards
                $awards = [
                    [
                        'TenderID' => $tender->Id,
                        'WinningSupplierID' => $supplier->Id,
                        'AwardedAmount' => rand(500000, 2000000),
                        'AwardStatus' => 'Approved',
                        'AwardDate' => now()->subDays(10),
                        'ContractStartDate' => now()->addDays(5),
                        'ContractEndDate' => now()->addDays(365),
                        'AwardJustification' => 'Best evaluated bid with competitive pricing and good technical proposal',
                        'TechnicalScore' => rand(80, 95),
                        'FinancialScore' => rand(85, 98),
                        'TotalScore' => rand(83, 96),
                        // Contract fields
                        'ContractStatus' => $awardIndex == 1 ? 'Active' : ($awardIndex == 2 ? 'Draft' : 'Approved'),
                        'ContractRef' => 'CONT-' . strtoupper(substr(md5($tender->Id . $supplier->Id), 0, 8)),
                        'ContractValue' => rand(500000, 2000000),
                        'PaymentTerms' => 'Net 30 days upon delivery and acceptance',
                        'DeliveryTerms' => 'Free delivery to client premises',
                        'SpecialConditions' => 'Performance bond required, 12 months warranty',
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(15),
                        'ModifiedOn' => now()->subDays(10),
                    ]
                ];
                
                foreach ($awards as $award) {
                    DB::table('t_TenderAwards')->insert($award);
                    $this->command->info("  ✅ Created award for tender {$tender->TenderNo} -> {$supplier->TradingName} (Status: {$award['ContractStatus']})");
                }
            }
        }
        
        // Also update the existing award to have an Active contract
        DB::table('t_TenderAwards')
            ->where('Id', 1)
            ->update([
                'ContractStatus' => 'Active',
                'ContractRef' => 'CONT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'ContractValue' => 1500000,
                'ContractStartDate' => now()->subDays(5),
                'ContractEndDate' => now()->addDays(360),
                'PaymentTerms' => 'Net 30 days',
                'DeliveryTerms' => 'FOB destination',
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]);
        
        $this->command->info("  ✅ Updated existing award to have Active contract status");
    }
    
    private function seedProcurementPlans()
    {
        $this->command->info("📋 Seeding procurement plans...");
        
        $existingPlans = DB::table('t_ConsolidatedProcurementPlan')->count();
        
        if ($existingPlans < 2) {
            $plans = [
                [
                    'Title' => 'FY 2024-2025 Office Equipment Procurement',
                    'ReferenceNumber' => 'PLAN-2024-001',
                    'FiscalYear' => '2024-2025',
                    'Status' => 'Approved',
                    'CreatedBy' => 1,
                    'CreatedDate' => now()->subDays(90),
                    'SubmittedBy' => 1,
                    'SubmittedDate' => now()->subDays(80),
                    'Remarks' => 'Annual procurement plan for office equipment and supplies',
                    'CreatedOn' => now()->subDays(90),
                    'ModifiedOn' => now()->subDays(70),
                ],
                [
                    'Title' => 'FY 2024-2025 IT Infrastructure Procurement',
                    'ReferenceNumber' => 'PLAN-2024-002', 
                    'FiscalYear' => '2024-2025',
                    'Status' => 'Approved',
                    'CreatedBy' => 1,
                    'CreatedDate' => now()->subDays(85),
                    'SubmittedBy' => 1,
                    'SubmittedDate' => now()->subDays(75),
                    'Remarks' => 'IT equipment and infrastructure procurement plan',
                    'CreatedOn' => now()->subDays(85),
                    'ModifiedOn' => now()->subDays(65),
                ]
            ];
            
            foreach ($plans as $plan) {
                $exists = DB::table('t_ConsolidatedProcurementPlan')
                    ->where('ReferenceNumber', $plan['ReferenceNumber'])
                    ->exists();
                    
                if (!$exists) {
                    DB::table('t_ConsolidatedProcurementPlan')->insert($plan);
                    $this->command->info("  ✅ Created plan: {$plan['ReferenceNumber']}");
                }
            }
        }
    }
    
    private function seedPlanLineItems()
    {
        $this->command->info("📝 Seeding plan line items...");
        
        // Get approved plans
        $plans = DB::table('t_ConsolidatedProcurementPlan')
            ->where('Status', 'Approved')
            ->get();
        
        foreach ($plans as $plan) {
            $existingItems = DB::table('t_PlanLineItem')
                ->where('PlanID', $plan->PlanID)
                ->count();
            
            if ($existingItems < 3) {
                $lineItems = [
                    [
                        'PlanID' => $plan->PlanID,
                        'CategoryID' => 1,
                        'BranchID' => 1,
                        'DepartmentID' => 1,
                        'MergedQty' => 25,
                        'UnitOfMeasure' => 'Units',
                        'EstimatedUnitCost' => 35000,
                        'ProcurementMethod' => 'Direct', // This is key for our query
                        'SchedulePeriod' => 'Q2 2024',
                        'ExecutionStatus' => 'Approved',
                        'ExpectedDeliveryDate' => now()->addDays(30),
                        'SourceType' => 'Budget',
                        'OriginalQTY' => 25,
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(60),
                        'ModifiedOn' => now()->subDays(60),
                    ],
                    [
                        'PlanID' => $plan->PlanID,
                        'CategoryID' => 2,
                        'BranchID' => 1,
                        'DepartmentID' => 2,
                        'MergedQty' => 10,
                        'UnitOfMeasure' => 'Sets',
                        'EstimatedUnitCost' => 125000,
                        'ProcurementMethod' => 'Direct', // This is key for our query  
                        'SchedulePeriod' => 'Q3 2024',
                        'ExecutionStatus' => 'Approved',
                        'ExpectedDeliveryDate' => now()->addDays(45),
                        'SourceType' => 'Budget',
                        'OriginalQTY' => 10,
                        'CreatedBy' => 1,
                        'ModifiedBy' => 1,
                        'CreatedOn' => now()->subDays(55),
                        'ModifiedOn' => now()->subDays(55),
                    ]
                ];
                
                foreach ($lineItems as $item) {
                    DB::table('t_PlanLineItem')->insert($item);
                }
                
                $this->command->info("  ✅ Added line items to plan: {$plan->ReferenceNumber}");
            }
        }
        
        // Also update existing plan item to have correct method
        DB::table('t_PlanLineItem')
            ->where('ProcurementMethod', '107')
            ->update(['ProcurementMethod' => 'Direct']);
        
        $this->command->info("  ✅ Updated existing plan items to use 'Direct' procurement method");
    }
    
    private function updateExistingData()
    {
        $this->command->info("🔄 Updating existing data for compatibility...");
        
        // Update existing awards to have proper contract status
        $updated = DB::table('t_TenderAwards')
            ->whereNull('ContractStatus')
            ->orWhere('ContractStatus', '')
            ->update([
                'ContractStatus' => 'Draft',
                'ModifiedBy' => 1,
                'ModifiedOn' => now()
            ]);
        
        if ($updated > 0) {
            $this->command->info("  ✅ Updated {$updated} awards to have contract status");
        }
        
        // Ensure we have at least one active contract
        $activeContracts = DB::table('t_TenderAwards')
            ->where('ContractStatus', 'Active')
            ->count();
            
        if ($activeContracts == 0) {
            DB::table('t_TenderAwards')
                ->where('AwardStatus', 'Approved')
                ->limit(1)
                ->update([
                    'ContractStatus' => 'Active',
                    'ContractStartDate' => now()->subDays(5),
                    'ContractEndDate' => now()->addDays(360),
                    'ContractRef' => 'CONT-ACTIVE-001',
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now()
                ]);
                
            $this->command->info("  ✅ Created at least one active contract");
        }
        
        $this->command->info("✅ Data compatibility updates completed!");
    }
}
