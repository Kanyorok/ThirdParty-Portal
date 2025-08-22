<?php

namespace Database\Seeders;

use App\Enums\CampaignStatusEnum;
use App\Enums\DMS\DocumentCheckOutStatusEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\Property\PropertyInvoiceEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Enums\Property\TenantClearanceEnum;
use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\Procurement\SchedulePlanEnum;
use App\Enums\TicketStatusEnum;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\Procurement\PrequalificationStatusEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\BusinessTypeEnum;
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

        DocumentCheckOutStatusEnum::getAll()->each(function ($item) use ($entries) {
            $entries->push([
                'CodeID' => 'DocumentCheckOutStatus',
                'Value' => $item->value,
                'Description' => $item->name,
            ]);
        });

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

        foreach (ThirdPartyTypeEnum::cases() as $index => $thirdPartyTypeEnum) {
            $entries->push([
                'CodeID' => 'ThirdPartyType',
                'Value' => $thirdPartyTypeEnum->value,
                'Description' => $thirdPartyTypeEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (PrequalificationStatusEnum::cases() as $index => $PrequalificationStatusEnum) {
            $entries->push([
                'CodeID' => 'PrequalificationStatus',
                'Value' => $PrequalificationStatusEnum->value,
                'Description' => $PrequalificationStatusEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (BusinessTypeEnum::cases() as $index => $businessTypeEnum) {
            $entries->push([
                'CodeID' => 'BusinessType',
                'Value' => $businessTypeEnum->value,
                'Description' => $businessTypeEnum->name,
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

        foreach (PrequalificationRoundEnum::cases() as $index => $prequalificationRoundEnum) {
            $entries->push([
                'CodeID' => 'PrequalificationRound',
                'Value' => $prequalificationRoundEnum->value,
                'Description' => $prequalificationRoundEnum->name,
                'DisplayOrder' => $index + 1,
            ]);
        }

        foreach (PrequalificationApplicationEnum::cases() as $index => $prequalificationApplicationEnum) {
            $entries->push([
                'CodeID' => 'PrequalificationApplication',
                'Value' => $prequalificationApplicationEnum->value,
                'Description' => $prequalificationApplicationEnum->name,
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
            ['CodeID' => 'GLAccountType', 'Description' => 'Assets', 'Value' => 'A', 'DisplayOrder' => 0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Liabilities', 'Value' => 'L', 'DisplayOrder' => 0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Income', 'Value' => 'I', 'DisplayOrder' => 0],
            ['CodeID' => 'GLAccountType', 'Description' => 'Expenses', 'Value' => 'E', 'DisplayOrder' => 0],

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
            ['CodeID' => 'Source', 'Description' => 'Transfer Receipts', 'Value' => 'Tr'],
            ['CodeID' => 'Source', 'Description' => 'Stock Adjustment', 'Value' => 'Sa'],
            ['CodeID' => 'Source', 'Description' => 'Transaction Transfer', 'Value' => 'Tt'],

            // Defects Condition
            ['CodeID' => 'DefectsCondition', 'Description' => 'Contaminated', 'Value' => 'C'],
            ['CodeID' => 'DefectsCondition', 'Description' => 'Irreparable', 'Value' => 'Ir'],

            // Adjustment Reason
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Damage', 'Value' => 'Da'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Damaged in Transit', 'Value' => 'Di'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Stock Found', 'Value' => 'Sf'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Expired', 'Value' => 'Ex'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Shrinkage', 'Value' => 'Sh'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'Other', 'Value' => 'Ot'],
            ['CodeID' => 'AdjustmentReason', 'Description' => 'In Transit', 'Value' => 'It'],

            // Payment Frequency
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Annually', 'Value' => 'A'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Bi-Annually', 'Value' => 'B'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Quarterly', 'Value' => 'Q'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Monthly', 'Value' => 'M'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Weekly', 'Value' => 'W'],
            ['CodeID' => 'PaymentFrequency', 'Description' => 'Daily', 'Value' => 'D'],

            // Payment Type
            ['CodeID' => 'PaymentType', 'Description' => 'Full', 'Value' => 'F'],
            ['CodeID' => 'PaymentType', 'Description' => 'Partial', 'Value' => 'P'],
            //['CodeID' => 'PaymentType', 'Description' => 'Scheduled', 'Value' => 'S'],

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
            ['CodeID' => 'DocumentType', 'Description' => 'Ownership', 'Value' => 'S'],
            ['CodeID' => 'DocumentType', 'Description' => 'Architectural Plan', 'Value' => 'A'],
            ['CodeID' => 'DocumentType', 'Description' => 'Insurance', 'Value' => 'I'],
            ['CodeID' => 'DocumentType', 'Description' => 'Others', 'Value' => 'O'],

            // Gender
            ['CodeID' => 'Gender', 'Description' => 'Male', 'Value' => 'M'],
            ['CodeID' => 'Gender', 'Description' => 'Female', 'Value' => 'F'],

            // MaritalStatus
            ['CodeID' => 'MaritalStatus', 'Description' => 'Single', 'Value' => 'S'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Married', 'Value' => 'M'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Divorced', 'Value' => 'D'],
            ['CodeID' => 'MaritalStatus', 'Description' => 'Widowed', 'Value' => 'W'],

            // Relationships
            ['CodeID' => 'Relationships', 'Description' => 'Spouse', 'Value' => 'S'],
            ['CodeID' => 'Relationships', 'Description' => 'Child', 'Value' => 'C'],
            ['CodeID' => 'Relationships', 'Description' => 'Parent', 'Value' => 'P'],
            ['CodeID' => 'Relationships', 'Description' => 'Sibling', 'Value' => 'S'],
            ['CodeID' => 'Relationships', 'Description' => 'Relative', 'Value' => 'R'],
            ['CodeID' => 'Relationships', 'Description' => 'Friend', 'Value' => 'F'],
            ['CodeID' => 'Relationships', 'Description' => 'LegalGuardian', 'Value' => 'LG'],


            // Occupation
            ['CodeID' => 'Occupation', 'Description' => 'Employed', 'Value' => 'E'],
            ['CodeID' => 'Occupation', 'Description' => 'Not Employed', 'Value' => 'N'],

            // ContactType
            ['CodeID' => 'ContactType', 'Description' => '📞 Call', 'Value' => 'C'],
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

            //Legal clauses types
            ['CodeID' => 'ClauseTypes', 'Description' => 'Specifies the responsibilities and obligations of each party.', 'Value' => 'Obligation Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Defines the start date, duration, and end date of the agreement.', 'Value' => 'Term Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Describes the payment terms, methods, and schedules.', 'Value' => 'Payment Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Covers conditions for terminating the agreement.', 'Value' => 'Termination Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Specifies confidentiality and non-disclosure obligations.', 'Value' => 'Confidentiality Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Sets out the process for resolving disputes.', 'Value' => 'Dispute Resolution Clause'],
            ['CodeID' => 'ClauseTypes', 'Description' => 'Outlines penalties or remedies for breaches.', 'Value' => 'Breach Clause'],

            //Legal Obligations
            ['CodeID' => 'LegalSourceTypes', 'Description' => 'Obligations and tasks required under the contract.', 'Value' => 'Contract'],
            ['CodeID' => 'LegalSourceTypes', 'Description' => 'Obligations and actions required for the case.', 'Value' => 'Case'],

            //Legal Intelleactuals IP types
            ['CodeID' => 'IPTypes', 'Description' => 'Exclusive rights granted for an invention or process.', 'Value' => 'Patent'],
            ['CodeID' => 'IPTypes', 'Description' => 'Legal protection for brand names, logos, and slogans.', 'Value' => 'Trademark'],
            ['CodeID' => 'IPTypes', 'Description' => 'Protection for artistic, literary, or musical works.', 'Value' => 'Copyright'],
            ['CodeID' => 'IPTypes', 'Description' => 'Protection for the visual design or shape of an object.', 'Value' => 'Industrial Design'],
            ['CodeID' => 'IPTypes', 'Description' => 'Rights protecting confidential business information.', 'Value' => 'Trade Secret'],

            //Legal Loan Security types
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Property pledged as security for a loan.', 'Value' => 'Real Estate Mortgage'],
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Motor vehicle pledged as collateral.', 'Value' => 'Vehicle Logbook'],
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Cash deposited and held as loan security.', 'Value' => 'Cash Deposit'],
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Business equipment or machinery pledged as collateral.', 'Value' => 'Equipment Charge'],
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Personal commitment from a guarantor to repay the loan.', 'Value' => 'Personal Guarantee'],
            ['CodeID' => 'LoanSecurityTypes', 'Description' => 'Financial instruments like shares or bonds used as security.', 'Value' => 'Securities Pledge'],

            //Legal Loan securities Loacations
            ['CodeID' => 'LoanSecurityLocations', 'Description' => 'Physical location where the secured asset is kept or registered.', 'Value' => 'On-Site Storage'],
            [ 'CodeID' => 'LoanSecurityLocations','Description' => 'Third-party secured warehouse or bonded storage facility.','Value' => 'Bonded Warehouse'],
            ['CodeID' => 'LoanSecurityLocations','Description' => 'Registered with the relevant government agency or land registry.','Value' => 'Government Registry'],
            [ 'CodeID' => 'LoanSecurityLocations', 'Description' => 'Held in the possession of the lender until the loan is repaid.', 'Value' => 'Lender Custody'],
            ['CodeID' => 'LoanSecurityLocations', 'Description' => 'Stored in a bank vault or secured bank deposit box.', 'Value' => 'Bank Vault'],

            //Legal Search Requests Types
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Verification of company registration details.', 'Value' => 'Company Search'],
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Search for pending or completed court cases involving an entity.', 'Value' => 'Court Case Search'],
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Verification of land title ownership and encumbrances.', 'Value' => 'Land Title Search'],
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Search for registered trademarks and related rights.', 'Value' => 'Trademark Search'],
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Search for registered patents and intellectual property.', 'Value' => 'Patent Search'],
            ['CodeID' => 'LegalSearchRequestTypes', 'Description' => 'Search for bankruptcy or insolvency status of an individual or company.', 'Value' => 'Bankruptcy Search'],

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

            // Stock Consumption
            ['CodeID' => 'IssuedToType', 'Description' => 'Employee', 'Value' => 'E'],
            ['CodeID' => 'IssuedToType', 'Description' => 'Department', 'Value' => 'D'],

            // Fleet Management

            ['CodeID' => 'EmploymentStatus', 'Description' => 'Retired', 'Value' => 'RE'],
            ['CodeID' => 'EmploymentStatus', 'Description' => 'On Leave', 'Value' => 'OL'],
            ['CodeID' => 'EmploymentStatus', 'Description' => 'Suspended', 'Value' => 'SU'],

            //Vehicle Types
            ['CodeID' => 'VehicleType', 'Description' => 'Car', 'Value' => 'CA'],
            ['CodeID' => 'VehicleType', 'Description' => 'Bus', 'Value' => 'BU'],
            ['CodeID' => 'VehicleType', 'Description' => 'Truck', 'Value' => 'TK'],
            ['CodeID' => 'VehicleType', 'Description' => 'Van', 'Value' => 'VA'],

            //Vehicle Statuses
            ['CodeID' => 'VehicleStatus', 'Description' => 'Active', 'Value' => 'AC'],
            ['CodeID' => 'VehicleStatus', 'Description' => 'Under Maintenance', 'Value' => 'UM'],
            ['CodeID' => 'VehicleStatus', 'Description' => 'Retired', 'Value' => 'RT'],


            //Insurance Statuses
            ['CodeID' => 'InsuranceStatus', 'Description' => 'Active', 'Value' => 'ACT'],
            ['CodeID' => 'InsuranceStatus', 'Description' => 'Pending', 'Value' => 'PEN'],
            ['CodeID' => 'InsuranceStatus', 'Description' => 'Expired', 'Value' => 'EXP'],

            //Vehicle Statuses
            ['CodeID' => 'InspectionStatus', 'Description' => 'Scheduled', 'Value' => 'SC'],
            ['CodeID' => 'InspectionStatus', 'Description' => 'Completed', 'Value' => 'CO'],
            ['CodeID' => 'InspectionStatus', 'Description' => 'Pending', 'Value' => 'PG'],
            ['CodeID' => 'InspectionStatus', 'Description' => 'Failed', 'Value' => 'FA'],

            //Driver Employment Type
            ['CodeID' => 'EmploymentType', 'Description' => 'Permanent', 'Value' => 'PR'],
            ['CodeID' => 'EmploymentType', 'Description' => 'Contract', 'Value' => 'CR'],
            ['CodeID' => 'EmploymentType', 'Description' => 'Hired', 'Value' => 'HR'],

            //Vehicle Request Status
            ['CodeID' => 'VehicleRequestStatus', 'Description' => 'Approved', 'Value' => 'Ap'],
            ['CodeID' => 'VehicleRequestStatus', 'Description' => 'Pending', 'Value' => 'pe'],
            ['CodeID' => 'VehicleRequestStatus', 'Description' => 'Rejected', 'Value' => 'Re'],

            //Driver Type
            ['CodeID' => 'DriverType', 'Description' => 'Contracted', 'Value' => 'Co'],
            ['CodeID' => 'DriverType', 'Description' => 'Permanent', 'Value' => 'Pe'],


            //Inventory Categories Status
            ['CodeID' => 'CategoryStatus', 'Description' => 'Active', 'Value' => 'Ac'],
            ['CodeID' => 'CategoryStatus', 'Description' => 'Inactive', 'Value' => 'In'],

            //Inventory Item Status
            ['CodeID' => 'ItemStatus', 'Description' => 'Active', 'Value' => 'AC'],
            ['CodeID' => 'ItemStatus', 'Description' => 'Inactive', 'Value' => 'IN'],

            //Maintenance Type
            ['CodeID' => 'FleetMaintenanceType', 'Description' => 'Routine', 'Value' => 'RO'],
            ['CodeID' => 'FleetMaintenanceType', 'Description' => 'Inspection', 'Value' => 'IN'],
            ['CodeID' => 'FleetMaintenanceType', 'Description' => 'Emergency', 'Value' => 'EM'],

            //Repair Type
            ['CodeID' => 'FleetRepairType', 'Description' => 'Normal', 'Value' => 'NO'],
            ['CodeID' => 'FleetRepairType', 'Description' => 'Emergency', 'Value' => 'EM'],

            //Maintenance Status
            ['CodeID' => 'FleetMaintenanceStatus', 'Description' => 'Scheduled', 'Value' => 'SC'],
            ['CodeID' => 'FleetMaintenanceStatus', 'Description' => 'Acknowledged', 'Value' => 'AC'],
            ['CodeID' => 'FleetMaintenanceStatus', 'Description' => 'Completed', 'Value' => 'CO'],

        ];

        foreach ($static as $index => $item) {
            $entries->push(array_merge($item, ['DisplayOrder' => $item['DisplayOrder'] ?? ($index + 1)]));
        }


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
