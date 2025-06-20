<?php

namespace Database\Seeders;

use App\Enums\CampaignStatusEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\TicketStatusEnum;
use App\Helpers\SystemHelper;
use App\Services\StaticListsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date = now();
        $user = SystemHelper::user();
        $data = collect();
        $id = 0;
        foreach (LeadStatusEnum::cases() as $statusEnum) {
            $id++;
            $data->add([
                'CodeID' => $statusEnum->value,
                'Description' => $statusEnum->description(),
                'DisplayOrder' => $id,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ]);
        }

        $id = 0;
        foreach (CampaignStatusEnum::cases() as $campaignStatusEnum) {
            $id++;
            $data->add([
                'CodeID' => $campaignStatusEnum->value,
                'Description' => $campaignStatusEnum->name,
                'DisplayOrder' => $id,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ]);
        }

        $id = 0;
        foreach (TicketStatusEnum::cases() as $ticketStatusEnum) {
            $id++;
            $data->add([
                'CodeID' => $ticketStatusEnum->value,
                'Description' => $ticketStatusEnum->name,
                'DisplayOrder' => $id,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ]);
        }

        DB::table('t_CodeDetails')->insert($data->toArray());

        DB::table('t_CodeDetails')->insert([
            [
                'CodeID' => StaticListsService::MarketingModes,
                'Description' => 'Outdoor Marketing',
                'DisplayOrder' => 1,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::MarketingModes,
                'Description' => 'Trade Shows',
                'DisplayOrder' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::CustomerResponses,
                'Description' => 'Needs a loan against loan',
                'DisplayOrder' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::CustomerResponses,
                'Description' => 'Interested (indicate product)',
                'DisplayOrder' => 1,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::LeadLossReason,
                'Description' => 'Lost to a Competitor',
                'DisplayOrder' => 1,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::LeadLossReason,
                'Description' => 'Cannot be Contacted',
                'DisplayOrder' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::CustomerType,
                'Description' => 'Business People',
                'DisplayOrder' => 1,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::CustomerType,
                'Description' => 'Boda Boda Rider',
                'DisplayOrder' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::CustomerType,
                'Description' => 'Taxi Driver',
                'DisplayOrder' => 3,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::Industries,
                'Description' => 'Agriculture',
                'DisplayOrder' => 1,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => StaticListsService::Industries,
                'Description' => 'Tourism',
                'DisplayOrder' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
        ]);

        DB::table('t_CodeDetails')->insert(values: [
            [
                'CodeID' => "RequisitionStatus",
                'Description' => 'Approved',
                'Value' => null,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => 'RequisitionStatus',
                'Description' => 'Pending',
                'Value' => null,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => 'RequisitionStatus',
                'Description' => 'Rejected',
                'Value' => null,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => 'RequisitionStatus',
                'Description' => 'Deferred',
                'Value' => null,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => 'RequisitionUrgency',
                'Description' => 'Very Urgent',
                'Value' => 5,
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
        ]);

        DB::table('t_CodeDetails')->insert([
            [
                'CodeID' => "SubmissionMode",
                'Description' => 'Hand delivered',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => 'SubmissionMode',
                'Description' => 'Courier',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],

        ]);


        //// Budget System Codes
            DB::table('t_CodeDetails')->insert([
            [
                'CodeID' => "GLAccountType",
                'Value'=>'A',
                'Description' => 'Assets',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "GLAccountType",
                'Value'=>'L',
                'Description' => 'Liabilities',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "GLAccountType",
                'Value'=>'I',
                'Description' => 'Income',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "GLAccountType",
                'Value'=>'E',
                'Description' => 'Expenses',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
        ]);

        DB::table('t_CodeDetails')->insert([
            [
                'CodeID' => "TenantType",
                'Description' => 'Individulal',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "TenantType",
                'Description' => 'Corporate',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "TenantType",
                'Description' => 'Government',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "TenantType",
                'Description' => 'NGO',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
            [
                'CodeID' => "TenantType",
                'Description' => 'Other',
                'CreatedOn' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $user->Id,
            ],
        ]);
    }
}
