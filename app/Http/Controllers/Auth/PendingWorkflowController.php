<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\PendingWorkflow;
use App\Models\CRM\Campaign;
use App\Models\CRM\MarketingPlanner;
use App\Models\CRM\Survey;
use App\Models\CRM\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PendingWorkflowController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $actor = $request->user();

        $query = PendingWorkflow::query()->where('t_PendingWorkflows.UserId', $actor->Id);

        $sources = collect();
        foreach (PermissionEnum::approvals() as $approval) {
            if ($actor->hasPermissionTo($approval)) {
                $sources->add($approval->module());
            }
        }

        $query->orWhere(function (Builder $query) use ($sources) {
            $query->whereNull('t_PendingWorkflows.UserId')
                ->whereIn('t_PendingWorkflows.Source', $sources->toArray());
        });

        return Datatables::of($query->with('source')->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('description', function (PendingWorkflow $workflow) {
                $source = $workflow->source;
                switch ($workflow->Source) {
                    case MarketingPlanner::getPrimaryKey():
                        if ($source instanceof MarketingPlanner) {
                            return ' <a href="' . route('marketing-planner.show', [$source->PlannerID]) . '"><h3>Marketing Plan </h3>' . $source->Name . '(' . $source->PlannerID . ')</a>';
                        }

                        return 'Unknown Marketing Plan';
                    case Ticket::getPrimaryKey():
                        if ($source instanceof Ticket) {
                            return '<a href="' . route('tickets.show', [$source->TicketID]) . '"><h3>Ticket </h3>' . $source->Title . '(' . $source->TicketID . ')</a>';
                        }

                        return 'Unknown Ticket';
                    case Campaign::getPrimaryKey():
                        if ($source instanceof Campaign) {
                            return '<a href="' . route('campaigns.show', [$source->CampaignID]) . '"><h3>Campaign </h3>' . $source->Label . '</a>';
                        }

                        return 'Unknown Campaign';
                    case Survey::getPrimaryKey():
                        if ($source instanceof Survey) {
                            return '<a href="' . route('surveys.show', [$source->SurveyID]) . '"><h3>Survey </h3>' . $source->Label . '</a>';
                        }

                        return 'Unknown Survey';
                    default:
                        return 'Unknown Module ?';
                }
            })->editColumn('CreatedOn', function (PendingWorkflow $workflow) {
                return $workflow->CreatedOn->format('M d, Y H:i');
            })->rawColumns(['description'])->make();
    }
}
