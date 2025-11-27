<?php

namespace Database\Seeders;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\CRM\Ticket;
use App\Models\Settings\WorkFlow;
use App\Models\Settings\WorkFlowStage;
use App\Models\Settings\WorkFlowType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $date = now();

        $ticketWorkFlow = WorkFlow::create([
            'Name' => 'Ticket Flow',
            'Source' => (new Ticket())->getTable(),
            'Description' => 'Ticket workflow',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        WorkflowStage::create([
            'Order' => 1,
            'StageName' => 'Reopen ticket',
            'EscalationLimit' => 10,
            'WorkFlowId' => $ticketWorkFlow->Id,
            'WorkFlowTypeId' => WorkFlowType::query()->where('TypeID', 'CNT')->firstOrFail()->Id,
            // 'WorkFlowLimitId' => null,
            'PermissionId' => Permission::query()->where('name', PermissionEnum::TicketApproval->value)->firstOrFail()->id,
            'Count' => 1,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

    }
}
