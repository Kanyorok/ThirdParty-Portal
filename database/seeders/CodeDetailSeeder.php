<?php

namespace Database\Seeders;

use App\Enums\CampaignStatusEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\Property\PropertyInvoiceEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Enums\Property\TenantClearanceEnum;
use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\Procurement\PrequalificationPeriodEnum;
use App\Enums\Procurement\SchedulePlanEnum;
use App\Enums\TicketStatusEnum;
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
        foreach (PropertyInvoiceEnum::cases() as $index => $propertyinvoiceEnum) {
            $entries->push([
                'CodeID' => 'PropertyInvoiceStatus',
                'Value' => $propertyinvoiceEnum->value,
                'Description' => $propertyinvoiceEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }
        foreach (PropertyNewLeaseEnum::cases() as $index => $propertynewleaseEnum) {
            $entries->push([
                'CodeID' => 'PropertyLeaseStatus',
                'Value' => $propertynewleaseEnum->value,
                'Description' => $propertynewleaseEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }
        foreach (TenantClearanceEnum::cases() as $index => $tenantclearanceEnum) {
            $entries->push([
                'CodeID' => 'TenantClearanceStatus',
                'Value' => $tenantclearanceEnum->value,
                'Description' => $tenantclearanceEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }
        foreach (DepartmentNeedsEnum::cases() as $index => $departmentneedsEnum) {
            $entries->push([
                'CodeID' => 'DepartmentNeedsStatus',
                'Value' => $departmentneedsEnum->value,
                'Description' => $departmentneedsEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }
        foreach (SchedulePlanEnum::cases() as $index => $scheduleplanEnum) {
            $entries->push([
                'CodeID' => 'SchedulePlanStatus',
                'Value' => $scheduleplanEnum->value,
                'Description' => $scheduleplanEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }
        foreach (PrequalificationPeriodEnum::cases() as $index => $prequalificationperiodEnum) {
            $entries->push([
                'CodeID' => 'PrequalificationPeriodStatus',
                'Value' => $prequalificationperiodEnum->value,
                'Description' => $prequalificationperiodEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        // STATIC ENTRIES (from various modules)
        $static = [
            // Requisition Status
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Approved', 'Value' => 'Ap'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Submitted For Approval', 'Value' => 'Su'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Pending', 'Value' => 'Pe'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Rejected', 'Value' => 'Re'],
            ['CodeID' => 'RequisitionStatus', 'Description' => 'Deferred', 'Value' => 'De'],

            // Requisition Urgency
            ['CodeID' => 'RequisitionUrgency', 'Description' => 'Very Urgent', 'Value' => 1],

            // Submission Mode
            ['CodeID' => 'SubmissionMode', 'Description' => 'Hand delivered'],
            ['CodeID' => 'SubmissionMode', 'Description' => 'Courier'],

            // GL Account Types
            ['CodeID' => 'GLAccountType', 'Description' => 'Assets', 'Value' => 'A','DisplayOrder'=>0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Liabilities', 'Value' => 'L','DisplayOrder'=>0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Income', 'Value' => 'I','DisplayOrder'=>0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Expenses', 'Value' => 'E','DisplayOrder'=>0],

            // Tenant Types
            ['CodeID' => 'TenantType', 'Description' => 'Individual', 'Value' => 'I'],
            ['CodeID' => 'TenantType', 'Description' => 'Corporate', 'Value' => 'C'],
            ['CodeID' => 'TenantType', 'Description' => 'Government', 'Value' => 'G'],
            ['CodeID' => 'TenantType', 'Description' => 'NGO', 'Value' => 'N'],
            ['CodeID' => 'TenantType', 'Description' => 'Other', 'Value' => 'O'],

            // Deposit Refunded
            ['CodeID' => 'DepositRefunded', 'Description' => 'Fully Refunded', 'Value' => 'F'],
            ['CodeID' => 'DepositRefunded', 'Description' => 'Partially Refunded', 'Value' => 'P'],
            ['CodeID' => 'DepositRefunded', 'Description' => 'Not Refunded', 'Value' => 'N'],

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
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Annually', 'Value' => 'A'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Bi-Annually', 'Value' => 'B'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Quarterly', 'Value' => 'Q'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Monthly', 'Value' => 'M'],

            // Termination Reason
            ['CodeID' => 'TerminationReason', 'Description' => 'Relocation', 'Value' => 'R'],
            ['CodeID' => 'TerminationReason', 'Description' => 'Non Payment', 'Value' => 'N'],
            ['CodeID' => 'TerminationReason', 'Description' => 'Other', 'Value' => 'O'],


            // Procurement Method
            ['CodeID' => 'ProcurementMethod', 'Description' => 'RFQ', 'Value' => 'R'],
            ['CodeID' => 'ProcurementMethod', 'Description' => 'Direct Purchase', 'Value' => 'D'],
            ['CodeID' => 'ProcurementMethod', 'Description' => 'Tender', 'Value' => 'T'],
            ['CodeID' => 'ProcurementMethod', 'Description' => 'Prequalification', 'Value' => 'P'],
            ['CodeID' => 'ProcurementMethod', 'Description' => 'Framework Agreement', 'Value' => 'F'],

            //Property Payment Method
            ['CodeID' => 'PaymentMethod', 'Description' => 'Mpesa', 'Value' => 'M'],
            ['CodeID' => 'PaymentMethod', 'Description' => 'Bank', 'Value' => 'B'],
            ['CodeID' => 'PaymentMethod', 'Description' => 'Cash', 'Value' => 'C'],

            //Property Issue Types
            ['CodeID' => 'IssueType', 'Description' => 'Electrical', 'Value' => 'E'],
            ['CodeID' => 'IssueType', 'Description' => 'Plumbing', 'Value' => 'P'],
            ['CodeID' => 'IssueType', 'Description' => 'Cleaning', 'Value' => 'C'],
            ['CodeID' => 'IssueType', 'Description' => 'Pest Control', 'Value' => 'M'],
            ['CodeID' => 'IssueType', 'Description' => 'Security', 'Value' => 'S'],
            ['CodeID' => 'IssueType', 'Description' => 'Other', 'Value' => 'O'],

            // Property Assignment Types
            ['CodeID' => 'AssignmentType', 'Description' => 'Internal Technician', 'Value' => 'I'],
            ['CodeID' => 'AssignmentType', 'Description' => 'Prequalified Vendor', 'Value' => 'P'],

            // Work Completion Status
            ['CodeID' => 'FinalStatus', 'Description' => 'Completed', 'Value' => 'C'],
            ['CodeID' => 'FinalStatus', 'Description' => 'Partially Completed', 'Value' => 'P'],
            ['CodeID' => 'FinalStatus', 'Description' => 'Not Completed', 'Value' => 'N'],

            // Priority Levels
            ['CodeID' => 'PriorityLevel', 'Description' => 'Low', 'Value' => 'L'],
            ['CodeID' => 'PriorityLevel', 'Description' => 'Medium', 'Value' => 'M'],
            ['CodeID' => 'PriorityLevel', 'Description' => 'High', 'Value' => 'H'],
            ['CodeID' => 'PriorityLevel', 'Description' => 'Critical', 'Value' => 'C'],

            // Payment  Modes 
            ['CodeID' => 'PaymentModes', 'Description' => 'Cash', 'Value' => 'C'],
            ['CodeID' => 'PaymentModes', 'Description' => 'Cheque', 'Value' => 'CH'],
            ['CodeID' => 'PaymentModes', 'Description' => 'Bank Transfer', 'Value' => 'BT'],
            ['CodeID' => 'PaymentModes', 'Description' => '	Standing Order', 'Value' => 'SO'],
            ['CodeID' => 'PaymentModes', 'Description' => '	Mobile Money (M-Pesa)', 'Value' => 'MM'],
            ['CodeID' => 'PaymentModes', 'Description' => '	Credit Card', 'Value' => 'CC'],
            ['CodeID' => 'PaymentModes', 'Description' => '	Debit Card', 'Value' => 'DC'],
            ['CodeID' => 'PaymentModes', 'Description' => '	Payroll Deduction', 'Value' => 'PD'],


            // Property DocumentType
            ['CodeID' => 'DocumentType', 'Description' => 'Ownership','Value' => 'S'],
            ['CodeID' => 'DocumentType', 'Description' => 'Architectural Plan', 'Value' => 'A'],
            ['CodeID' => 'DocumentType', 'Description' => 'Insurance', 'Value' => 'I'],
            ['CodeID' => 'DocumentType', 'Description' => 'Others', 'Value' => 'O'],

            // Gender
            ['CodeID' => 'Gender', 'Description' => 'Male','Value' => 'M'],
            ['CodeID' => 'Gender', 'Description' => 'Female', 'Value' => 'F'],

            // MaritalStatus
            ['CodeID' => 'MaritalStatus', 'Description' => 'Single','Value' => 'S'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Married', 'Value' => 'M'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Divorced', 'Value' => 'D'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Widowed', 'Value' => 'W'],
            
            // Relationships
            ['CodeID' => 'Relationships', 'Description' => 'Spouse','Value' => 'S'],
            ['CodeID' => 'Relationships', 'Description' => 'Child', 'Value' => 'C'],
            ['CodeID' => 'Relationships', 'Description' => 'Parent', 'Value' => 'P'],
            ['CodeID' => 'Relationships', 'Description' => 'Sibling', 'Value' => 'S'],    
            ['CodeID' => 'Relationships', 'Description' => 'Relative', 'Value' => 'R'],
            ['CodeID' => 'Relationships', 'Description' => 'Friend', 'Value' => 'F'],
            ['CodeID' => 'Relationships', 'Description' => 'LegalGuardian', 'Value' => 'LG'],
            
            
            
            // Occupation
            ['CodeID' => 'Occupation', 'Description' => 'Employed','Value' => 'E'],
            ['CodeID' => 'Occupation', 'Description' => 'Not Employed', 'Value' => 'N'],

            // ContactType
            ['CodeID' => 'ContactType', 'Description' => '📞 Call','Value' => 'C'],
            ['CodeID' => 'ContactType', 'Description' => '📧 Email', 'Value' => 'E'],
            ['CodeID' => 'ContactType', 'Description' => '📲 SMS', 'Value' => 'S'],
            ['CodeID' => 'ContactType', 'Description' => '🏢 Visit', 'Value' => 'V'],
            
            //Payment Terms
            ['CodeID' => 'PaymentTerm', 'Description' => 'Cash on Delivery – payment immediately on receipt', 'Value' => 'CD'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Payment due 30 days from invoice date', 'Value' => 'N3'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Payment due 60 days after invoice date', 'Value' => 'N6'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Payment due 90 days after invoice date', 'Value' => 'N9'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Cash in Advance – payment before delivery', 'Value' => 'CIA'],
            ['CodeID' => 'PaymentTerm', 'Description' => '50% Advance, 50% on Delivery', 'Value' => '50/50'],
            ['CodeID' => 'PaymentTerm', 'Description' => '30% Advance, 70% after installation', 'Value' => '30/70'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Payment upon specific project phases or delivery', 'Value' => 'MB'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Payment due 15 days after end of month', 'Value' => 'EOM'],
            ['CodeID' => 'PaymentTerm', 'Description' => 'Based on certified project work progress', 'Value' => 'PP'],
            ['CodeID' => 'PaymentTerm', 'Description' => '90% on completion, 10% after retention period', 'Value' => 'R'],
          
            // Insurance Product 
            ['CodeID' => 'InsuranceProduct', 'Description' => 'Life Insurance', 'Value' => 'L'],
            ['CodeID' => 'InsuranceProduct', 'Description' => 'Health Insurance', 'Value' => 'H'],
            ['CodeID' => 'InsuranceProduct', 'Description' => 'Property Insurance', 'Value' => 'P'],
            ['CodeID' => 'InsuranceProduct', 'Description' => 'Vehicle Insurance', 'Value' => 'V'],
            ['CodeID' => 'InsuranceProduct', 'Description' => 'Travel Insurance', 'Value' => 'T'],

            // Insurance Provider
            ['CodeID' => 'InsuranceProvider', 'Description' => 'Jubilee', 'Value' => 'J'],
            ['CodeID' => 'InsuranceProvider', 'Description' => 'Britam', 'Value' => 'B'],
            ['CodeID' => 'InsuranceProvider', 'Description' => 'CIC Insurance', 'Value' => 'C'],

            //Insurance Decision
            ['CodeID' => 'Decision','Description' => 'Approved', 'Value' => 'A'],
            ['CodeID' => 'Decision','Description' => 'Decline', 'Value' => 'D'],
            ['CodeID' => 'Decision','Description' => 'More Information Needed', 'Value' => 'M'],

            //Insurance Claim type
            ['CodeID' => 'ClaimType','Description' => 'Death', 'Value' => 'D'],
            ['CodeID' => 'ClaimType','Description' => 'Accident', 'Value' => 'A'],
            ['CodeID' => 'ClaimType','Description' => 'Loss', 'Value' => 'L'],
            ['CodeID' => 'ClaimType','Description' => 'Medical', 'Value' => 'M'],
            ['CodeID' => 'ClaimType','Description' => 'Other', 'Value' => 'O'],

            //Insurance Claim Status
            ['CodeID' => 'ClaimStatus','Description' => 'Initiated', 'Value' => 'I'],
            ['CodeID' => 'ClaimStatus','Description' => 'Under Assessment', 'Value' => 'U'],
            ['CodeID' => 'ClaimStatus','Description' => 'Approved', 'Value' => 'A'],
            ['CodeID' => 'ClaimStatus','Description' => 'Rejected', 'Value' => 'R'],
            ['CodeID' => 'ClaimStatus','Description' => 'Paid', 'Value' => 'P'],

            // PolicyTypeId
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Life Insurance', 'Value' => 'L'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Motor Insurance', 'Value' => 'B'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Property Insurance', 'Value' => 'P'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Travel Insurance', 'Value' => 'T'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Personal Accident Insurance', 'Value' => 'A'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Business Interruption Insurance', 'Value' => 'I'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Marine Cargo Insurance', 'Value' => 'M'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Health Insurance', 'Value' => 'H'],
            ['CodeID' => 'PolicyTypeId', 'Description' => 'Education Policy', 'Value' => 'E'],


            // AppliesTo
            ['CodeID' => 'AppliesTo', 'Description' => 'AppliesTo', 'Value' => 'J'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Vehicle', 'Value' => 'B'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Jubilee', 'Value' => 'J'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Britam', 'Value' => 'B'],
            ['CodeID' => 'AppliesTo', 'Description' => 'CIC Insurance', 'Value' => 'C'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Individual ', 'Value' => 'I'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Corporate ', 'Value' => 'C'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Government ', 'Value' => 'G'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Spouse ', 'Value' => 'S'],
            ['CodeID' => 'AppliesTo', 'Description' => 'Child ', 'Value' => 'C'],


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
