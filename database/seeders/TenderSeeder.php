<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\Tender;

class TenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the table to clear any existing data


        // Raw data for tenders with provided enum values
        $tenders = [
            [
                'TenderNo' => 'TND/PROC/2025/001',
                'Title' => 'Supply of ICT Equipment',
                'TenderType' => 'op', // As provided (assumed to match TenderTypeEnum)
                'TenderCategory' => 'Goods', // As provided (assumed to match TenderCategoryEnum)
                'ScopeOfWork' => 'Procurement and delivery of laptops and desktops.',
                'Instructions' => 'Submit bids in sealed envelopes by the deadline.',
                'SubmissionDeadline' => '2025-06-01',
                'OpeningDate' => '2025-06-02',
                'Status' => 'pb', // As provided (assumed to match TenderStatusEnum)
                'RelatedPRID' => 'PR-1001',
                'ProcurementModeId' => (int) 1, // Explicitly cast to integer
                'DateCreated' => '2025-05-01 10:00:00',
                'CreatedBy' => 'John Doe',
                'ModifiedBy' => 'Jane Smith',
            ],
            [
                'TenderNo' => 'TND/PROC/2025/002',
                'Title' => 'Supply of Office Furniture',
                'TenderType' => 'rs', // As provided (assumed to match TenderTypeEnum)
                'TenderCategory' => 'Goods', // As provided (assumed to match TenderCategoryEnum)
                'ScopeOfWork' => 'Procurement of chairs and desks for office use.',
                'Instructions' => 'Bids to be submitted via email.',
                'SubmissionDeadline' => '2025-06-10',
                'OpeningDate' => '2025-06-11',
                'Status' => 'dr', // As provided (assumed to match TenderStatusEnum)
                'RelatedPRID' => 'PR-1002',
                'ProcurementModeId' => (int) 2, // Explicitly cast to integer
                'DateCreated' => '2025-05-02 14:30:00',
                'CreatedBy' => 'Alice Johnson',
                'ModifiedBy' => 'Bob Wilson',
            ],
            [
                'TenderNo' => 'TND/PROC/2025/003',
                'Title' => 'Construction Services',
                'TenderType' => 'rs', // As provided (assumed to match TenderTypeEnum)
                'TenderCategory' => 'Works', // As provided (assumed to match TenderCategoryEnum)
                'ScopeOfWork' => 'Construction of a new office building.',
                'Instructions' => 'Submit detailed proposals with blueprints.',
                'SubmissionDeadline' => '2025-06-15',
                'OpeningDate' => '2025-06-16',
                'Status' => 'pb', // As provided (assumed to match TenderStatusEnum)
                'RelatedPRID' => 'PR-1003',
                'ProcurementModeId' => (int) 3, // Explicitly cast to integer
                'DateCreated' => '2025-05-03 09:15:00',
                'CreatedBy' => 'Mike Brown',
                'ModifiedBy' => 'Sarah Davis',
            ],
        ];

        // Insert raw data into the t_Tenders table
        foreach ($tenders as $tender) {
            Tender::create($tender);
        }
    }
}
