<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Models\Auth\User;
use Faker\Factory as Faker;

class BidSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get available data
        $tenders = Tender::with('currency')->limit(4)->get();
        $suppliers = Supplier::with('thirdParty')->limit(10)->get();
        $users = User::limit(5)->get();
        
        if ($tenders->isEmpty() || $suppliers->isEmpty() || $users->isEmpty()) {
            $this->command->info('⚠️  Missing required data. Please ensure you have tenders, suppliers, and users in the database.');
            return;
        }

        $this->command->info('🎯 Creating comprehensive bid submission test data...');

        $submissionScenarios = [
            // Scenario 1: Fresh tender with multiple bids at different stages
            [
                'tender' => $tenders[0],
                'description' => 'Fresh tender with multiple competitive bids',
                'bids' => [
                    ['status' => 'submitted', 'amount' => 450000, 'responsive' => true, 'source' => 'portal'],
                    ['status' => 'submitted', 'amount' => 520000, 'responsive' => true, 'source' => 'portal'],
                    ['status' => 'submitted', 'amount' => 380000, 'responsive' => false, 'source' => 'manual'],
                    ['status' => 'draft', 'amount' => 475000, 'responsive' => null, 'source' => 'portal'],
                ]
            ],
            
            // Scenario 2: Tender with opened bids ready for evaluation
            [
                'tender' => $tenders[1],
                'description' => 'Tender with opened bids ready for evaluation',
                'bids' => [
                    ['status' => 'responsive', 'amount' => 750000, 'responsive' => true, 'source' => 'portal', 'opened' => true],
                    ['status' => 'responsive', 'amount' => 820000, 'responsive' => true, 'source' => 'manual', 'opened' => true],
                    ['status' => 'non-responsive', 'amount' => 650000, 'responsive' => false, 'source' => 'portal', 'opened' => true],
                ]
            ],
            
            // Scenario 3: Tender with evaluated bids 
            [
                'tender' => $tenders[2],
                'description' => 'Tender with fully evaluated bids',
                'bids' => [
                    ['status' => 'evaluated', 'amount' => 280000, 'responsive' => true, 'source' => 'portal', 'opened' => true, 'tech_score' => 85, 'fin_score' => 90],
                    ['status' => 'evaluated', 'amount' => 320000, 'responsive' => true, 'source' => 'manual', 'opened' => true, 'tech_score' => 92, 'fin_score' => 78],
                    ['status' => 'evaluated', 'amount' => 295000, 'responsive' => true, 'source' => 'portal', 'opened' => true, 'tech_score' => 88, 'fin_score' => 85],
                ]
            ],
            
            // Scenario 4: Single tender with awarded bid
            [
                'tender' => $tenders[3],
                'description' => 'Completed tender with awarded bid',
                'bids' => [
                    ['status' => 'awarded', 'amount' => 1250000, 'responsive' => true, 'source' => 'portal', 'opened' => true, 'tech_score' => 95, 'fin_score' => 88],
                    ['status' => 'evaluated', 'amount' => 1380000, 'responsive' => true, 'source' => 'manual', 'opened' => true, 'tech_score' => 82, 'fin_score' => 85],
                    ['status' => 'rejected', 'amount' => 1150000, 'responsive' => false, 'source' => 'portal', 'opened' => true],
                ]
            ]
        ];

        foreach ($submissionScenarios as $scenario) {
            $tender = $scenario['tender'];
            $this->command->info("📝 Creating bids for tender: {$tender->TenderNo} - {$scenario['description']}");
            
            foreach ($scenario['bids'] as $index => $bidConfig) {
                $supplier = $suppliers[$index % count($suppliers)];
                $user = $users[$index % count($users)];
                
                // Calculate submission date (past few days)
                $submittedDaysAgo = $faker->numberBetween(1, 7);
                $submissionDate = now()->subDays($submittedDaysAgo);
                
                $bid = BidSubmission::create([
                    'TenderRef' => $tender->TenderNo,
                    'SupplierName' => $supplier->thirdParty->TradingName ?? $supplier->thirdParty->ThirdPartyName,
                    'SupplierId' => $supplier->Id,
                    'SubmissionMode' => $this->getSubmissionModeId($bidConfig['source']),
                    'ReceivedAt' => $submissionDate,
                    'RecordedBy' => $bidConfig['source'] === 'portal' ? 'Portal Submission System' : $faker->name,
                    'Remarks' => $this->generateRemarks($bidConfig, $faker),
                    'SubmissionSource' => $bidConfig['source'],
                    'DocumentsAccessible' => $bidConfig['opened'] ?? false,
                    'BidOpeningDate' => $tender->OpeningDate,
                    
                    // Business fields
                    'BidAmount' => $bidConfig['amount'],
                    'Currency' => $tender->currency->Code ?? 'KES',
                    'ValidityPeriod' => $faker->numberBetween(60, 180),
                    'DeliveryPeriod' => $faker->numberBetween(30, 120),
                    'PaymentTerms' => $this->generatePaymentTerms($faker),
                    'BidStatus' => $bidConfig['status'],
                    
                    // Evaluation fields
                    'IsResponsive' => $bidConfig['responsive'],
                    'ResponsivenessRemarks' => $this->generateResponsivenessRemarks($bidConfig, $faker),
                    'TechnicalScore' => $bidConfig['tech_score'] ?? null,
                    'FinancialScore' => $bidConfig['fin_score'] ?? null,
                    'TotalScore' => isset($bidConfig['tech_score'], $bidConfig['fin_score']) ? 
                        ($bidConfig['tech_score'] + $bidConfig['fin_score']) : null,
                    'EvaluationNotes' => isset($bidConfig['tech_score']) ? $this->generateEvaluationNotes($bidConfig, $faker) : null,
                    
                    // Opening ceremony tracking
                    'OpenedAt' => ($bidConfig['opened'] ?? false) ? $submissionDate->addDays(1) : null,
                    'OpenedBy' => ($bidConfig['opened'] ?? false) ? $user->Id : null,
                    
                    // Mock encrypted documents
                    'EncryptedDocuments' => $this->generateMockEncryptedDocs($bidConfig, $faker),
                    
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);
                
                $this->command->info("  ✅ Created {$bidConfig['status']} bid: {$supplier->thirdParty->TradingName} - KES " . number_format($bidConfig['amount']));
            }
        }

        $total = BidSubmission::count();
        $this->command->info("🎉 Successfully created {$total} bid submissions across multiple tender scenarios!");
        $this->command->info("📊 Distribution:");
        $this->command->info("   • Submitted: " . BidSubmission::where('BidStatus', 'submitted')->count());
        $this->command->info("   • Responsive: " . BidSubmission::where('BidStatus', 'responsive')->count());
        $this->command->info("   • Non-responsive: " . BidSubmission::where('BidStatus', 'non-responsive')->count());
        $this->command->info("   • Evaluated: " . BidSubmission::where('BidStatus', 'evaluated')->count());
        $this->command->info("   • Awarded: " . BidSubmission::where('BidStatus', 'awarded')->count());
        $this->command->info("   • Portal submissions: " . BidSubmission::where('SubmissionSource', 'portal')->count());
    }

    private function getSubmissionModeId(string $source): int
    {
        // Return a mock submission mode ID
        return $source === 'portal' ? 2 : 1; // Assuming 1=manual, 2=portal
    }

    private function generateRemarks(array $config, $faker): string
    {
        if ($config['source'] === 'portal') {
            return 'Submitted via supplier portal with encrypted documents';
        }
        
        $remarks = [
            'Hand delivered to procurement office',
            'Submitted via email with password protection', 
            'Delivered by courier service',
            'Submitted through tender box'
        ];
        
        return $faker->randomElement($remarks);
    }

    private function generatePaymentTerms($faker): string
    {
        $terms = [
            '30 days from delivery',
            '45 days from invoice date',
            '60 days from completion',
            '15 days from acceptance',
            '30% advance, 70% on completion'
        ];
        
        return $faker->randomElement($terms);
    }

    private function generateResponsivenessRemarks(array $config, $faker): ?string
    {
        if ($config['responsive'] === null) {
            return null;
        }
        
        if ($config['responsive']) {
            return 'All required documents submitted and meet specifications';
        }
        
        $issues = [
            'Missing tax compliance certificate',
            'Bid validity period insufficient',
            'Technical specifications not met',
            'Financial documents incomplete',
            'Late submission after deadline'
        ];
        
        return $faker->randomElement($issues);
    }

    private function generateEvaluationNotes(array $config, $faker): string
    {
        $notes = [
            'Strong technical proposal with innovative approach',
            'Competitive pricing with good technical merit',
            'Meets all requirements with standard approach',
            'Excellent track record and references provided',
            'Good value proposition with reasonable timeline'
        ];
        
        return $faker->randomElement($notes);
    }

    private function generateMockEncryptedDocs(array $config, $faker): string
    {
        $docCount = $faker->numberBetween(2, 5);
        $docs = [];
        
        for ($i = 0; $i < $docCount; $i++) {
            $docs[] = [
                'id' => $faker->uuid,
                'original_filename' => $faker->randomElement([
                    'technical_proposal.pdf',
                    'financial_proposal.pdf', 
                    'company_profile.pdf',
                    'tax_certificate.pdf',
                    'references.pdf'
                ]),
                'encrypted_path' => 'encrypted-bids/' . date('Y/m/d') . '/' . $faker->uuid . '.enc',
                'file_size' => $faker->numberBetween(100000, 5000000),
                'uploaded_at' => now()->toISOString()
            ];
        }
        
        return json_encode($docs);
    }

}
