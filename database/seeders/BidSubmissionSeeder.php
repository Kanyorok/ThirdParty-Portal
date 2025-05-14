<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BidSubmission;
use Carbon\Carbon;

class BidSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $sampleData = [
            [
                'TenderRef' => 'TND/PROC/2025/001',
                'SupplierName' => 'Tech Supplies Ltd',
                'SubmissionMode' => 'Email',
                'ReceivedAt' => Carbon::parse('2025-05-10 14:30:00'),
                'RecordedBy' => 'John Doe',
                'Remarks' => 'Submitted all required documents.',
                'Documents' => 'proposal.pdf, financials.pdf',
            ],
            [
                'TenderRef' => 'TND/PROC/2025/002',
                'SupplierName' => 'Office Solutions Inc',
                'SubmissionMode' => 'Hand delivered',
                'ReceivedAt' => Carbon::parse('2025-05-11 09:15:00'),
                'RecordedBy' => 'Jane Smith',
                'Remarks' => null,
                'Documents' => 'bid_document.pdf',
            ],
            [
                'TenderRef' => 'TND/PROC/2025/001',
                'SupplierName' => 'Nova Systems',
                'SubmissionMode' => 'Courier',
                'ReceivedAt' => Carbon::parse('2025-05-12 11:45:00'),
                'RecordedBy' => 'John Doe',
                'Remarks' => 'Courier tracking number: XYZ123',
                'Documents' => null,
            ],
        ];

        foreach ($sampleData as $data) {
            BidSubmission::create($data);
        }
    }
}