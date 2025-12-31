<?php

namespace App\Traits\Controller;

use App\Models\CRM\Approval\Workflow;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait WorkflowTrait
{
    /**
     * @throws Exception
     */
    public function workflows(Builder|MorphMany $query): JsonResponse
    {
        return Datatables::of($query->with(['creator'])->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->editColumn('Status', function (Workflow $workflow) {
                return ' <details><summary>' . $workflow->Status->name . '</summary><p>' . $workflow->Notes . '</p></details>';
            })->editColumn('CreatedOn', function (Workflow $workflow) {
                return $workflow->CreatedOn?->format('M d, Y H:i');
            })->rawColumns(['Status'])->make();
    }
}
