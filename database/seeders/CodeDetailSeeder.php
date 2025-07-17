<?php

namespace Database\Seeders;

use App\Enums\CampaignStatusEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\TicketStatusEnum;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Helpers\SystemHelper;
use App\Services\StaticListsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeDetailSeeder extends Seeder
{
    public function run(): void
    {
        $date = now();
        $user = SystemHelper::user();

        $entries = collect();

        // ENUMS
        foreach (LeadStatusEnum::cases() as $index => $statusEnum) {
            $entries->push([
                'CodeID' => 'LeadStatus',
                'Value' => $statusEnum->value,
                'Description' => $statusEnum->description(),
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (CampaignStatusEnum::cases() as $index => $campaignStatusEnum) {
            $entries->push([
                'CodeID' => 'CampaignStatus',
                'Value' => $campaignStatusEnum->value,
                'Description' => $campaignStatusEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (TicketStatusEnum::cases() as $index => $ticketStatusEnum) {
            $entries->push([
                'CodeID' => 'TicketStatus',
                'Value' => $ticketStatusEnum->value,
                'Description' => $ticketStatusEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (ThirdPartyTypeEnum::cases() as $index => $thirdPartyTypeEnum) {
            $entries->push([
                'CodeID' => 'ThirdPartyType',
                'Value' => $thirdPartyTypeEnum->value,
                'Description' => $thirdPartyTypeEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (ThirdPartyStatusEnum::cases() as $index => $thirdPartyStatusEnum) {
            $entries->push([
                'CodeID' => 'ThirdPartyStatus',
                'Value' => $thirdPartyStatusEnum->value,
                'Description' => $thirdPartyStatusEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (ThirdPartyApprovalStatusEnum::cases() as $index => $approvalStatusEnum) {
            $entries->push([
                'CodeID' => 'ThirdPartyApprovalStatus',
                'Value' => $approvalStatusEnum->value,
                'Description' => $approvalStatusEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        // STATIC ENTRIES (from various modules)
        $static = [
            // Requisition Status
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Approved'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Submitted For Approval'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Pending'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Rejected'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Deferred'],

            // Requisition Urgency
            ['CodeID' => 'RequisitionUrgency', 'Description' => 'Very Urgent', 'Value' => 1],

            // Submission Mode
            ['CodeID' => 'SubmissionMode', 'Description' => 'Hand delivered'],
            ['CodeID' => 'SubmissionMode', 'Description' => 'Courier'],

            // GL Account Types
            ['CodeID' => 'GLAccountType', 'Description' => 'Assets', 'Value' => 'A'],
            ['CodeID' => 'GLAccountType', 'Description' => 'Liabilities', 'Value' => 'L'],
            ['CodeID' => 'GLAccountType', 'Description' => 'Income', 'Value' => 'I'],
            ['CodeID' => 'GLAccountType', 'Description' => 'Expenses', 'Value' => 'E'],

            // Tenant Types
            ['CodeID' => 'TenantType', 'Description' => 'Individual'],
            ['CodeID' => 'TenantType', 'Description' => 'Corporate'],
            ['CodeID' => 'TenantType', 'Description' => 'Government'],
            ['CodeID' => 'TenantType', 'Description' => 'NGO'],
            ['CodeID' => 'TenantType', 'Description' => 'Other'],

            // Deposit Refunded
            ['CodeID' => 'DepositRefunded', 'Description' => 'Fully Refunded'],
            ['CodeID' => 'DepositRefunded', 'Description' => 'Partially Refunded'],
            ['CodeID' => 'DepositRefunded', 'Description' => 'Not Refunded'],

            // Source
            ['CodeID' => 'Source', 'Description' => 'Transfer Receipts'],
            ['CodeID' => 'Source', 'Description' => 'Stock Adjustment'],
            ['CodeID' => 'Source', 'Description' => 'Transaction Transfer'],

            // Defects Condition
            ['CodeID' => 'DefectsCondition', 'Description' => 'Contaminated'],
            ['CodeID' => 'DefectsCondition', 'Description' => 'Irreparable'],

            // Adjustment Reason
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Damage'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Damaged in Transit'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Stock Found'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Expired'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Shrinkage'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Other'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'In Transit'],

            // Payment Frequency
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Annually'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Bi-Annually'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Quarterly'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Monthly'],

            // Termination Reason
            ['CodeID' => 'TerminationReason', 'Description' => 'Relocation'],
            ['CodeID' => 'TerminationReason', 'Description' => 'Non Payment'],
            ['CodeID' => 'TerminationReason', 'Description' => 'Other'],

            // Procurement Method
            ['CodeID' => 'ProcurementMethod', 'Description' => 'RFQ'],
            ['CodeID' => 'ProcurementMethod', 'Description' => 'Tender'],

            // StaticListsService entries
            ['CodeID' => StaticListsService::MarketingModes, 'Description' => 'Outdoor Marketing', 'DisplayOrder' => 1],
            ['CodeID' => StaticListsService::MarketingModes, 'Description' => 'Trade Shows', 'DisplayOrder' => 2],
            ['CodeID' => StaticListsService::CustomerResponses, 'Description' => 'Interested (indicate product)', 'DisplayOrder' => 1],
            ['CodeID' => StaticListsService::CustomerResponses, 'Description' => 'Needs a loan against loan', 'DisplayOrder' => 2],
            ['CodeID' => StaticListsService::LeadLossReason, 'Description' => 'Lost to a Competitor', 'DisplayOrder' => 1],
            ['CodeID' => StaticListsService::LeadLossReason, 'Description' => 'Cannot be Contacted', 'DisplayOrder' => 2],
            ['CodeID' => StaticListsService::CustomerType, 'Description' => 'Business People', 'DisplayOrder' => 1],
            ['CodeID' => StaticListsService::CustomerType, 'Description' => 'Boda Boda Rider', 'DisplayOrder' => 2],
            ['CodeID' => StaticListsService::CustomerType, 'Description' => 'Taxi Driver', 'DisplayOrder' => 3],
            ['CodeID' => StaticListsService::Industries, 'Description' => 'Agriculture', 'DisplayOrder' => 1],
            ['CodeID' => StaticListsService::Industries, 'Description' => 'Tourism', 'DisplayOrder' => 2],
        ];

        foreach ($static as $index => $item) {
            $entries->push(array_merge($item, ['DisplayOrder' => $item['DisplayOrder'] ?? ($index + 1)]));
        }

        // Ensure only non-existing records are inserted
        foreach ($entries as $entry) {
            $exists = DB::table('t_CodeDetails')->where('CodeID', $entry['CodeID'])->where('Description', $entry['Description'])->exists();
            if (!$exists) {
                DB::table('t_CodeDetails')->insert([
                    'CodeID' => $entry['CodeID'],
                    'Value' => $entry['Value'] ?? null,
                    'Description' => $entry['Description'],
                    'DisplayOrder' => $entry['DisplayOrder'] ?? 1,
                    'CreatedOn' => $date,
                    'CreatedBy' => $user->Id,
                    'ModifiedOn' => $date,
                    'ModifiedBy' => $user->Id,
                ]);
            }
        }
    }
}
