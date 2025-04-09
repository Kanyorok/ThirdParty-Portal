<?php

namespace App\Http\Controllers\Settings;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CodeDetailsRequest;
use App\Models\CodeDetail;
use App\Services\StaticListsService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Yajra\DataTables\DataTables;

class CodeDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
        //$this->authorizeResource(CodeDetail::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', CodeDetail::class);
        if (!in_array($request->_code, StaticListsService::getLists()->toArray())) {
            throw new RuntimeException('Invalid list requested');
        }

        return Datatables::of(CodeDetail::query()->where('CodeID', $request->_code)->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (CodeDetail $codeDetail) {
                return '<button type="button" class="btn btn-primary btn-sm  list-action-update" data-info="' . $codeDetail->ID . '~' . $codeDetail->Description . '"><i class="fas fa-edit"></i> edit</button>
                         <button type="button" class="btn btn-danger btn-sm  list-action-trash" data-info="' . $codeDetail->ID . '~' . $codeDetail->Description . '"><i class="fas fa-trash"></i> trash</button>';
            })->setRowAttr([
                'data-info' => function (CodeDetail $codeDetail) {
                    return $codeDetail->ID;
                },
            ])->rawColumns(['action'])->make();
    }


    /**
     * @throws AuthorizationException
     */
    public function order(Request $request): JsonResponse
    {
        $codeDetail = CodeDetail::query()->where('ID', $request->get('CodeId'))->first();
        if (!$codeDetail instanceof CodeDetail) {
            return $this->errored('could not change order.');
        }
        $this->authorize('update', $codeDetail);
        $actor = $request->user();
        $position = (int)$request->get('position');
        if ($position === 0) {
            return $this->errored('could not change order');
        }

        try {
            DB::transaction(static function () use ($codeDetail, $actor, $position) {
                (new StaticListsService($codeDetail))->setOrder($actor, $position);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create code detail :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('order updated');
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function store(CodeDetailsRequest $request): JsonResponse
    {
        $this->authorize('view', CodeDetail::class);

        $list = $request->getType();
        $description = $request->getDescription($list);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($list, $description, $actor) {
                StaticListsService::create($list, $description, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create code detail :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('added to list successfully', data: ['list' => $list]);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function update(CodeDetailsRequest $request, $codeDetailId): JsonResponse
    {
        $codeDetail = CodeDetail::query()->where('ID', $codeDetailId)->first();
        if (!$codeDetail instanceof CodeDetail) {
            return $this->errored('could not validate that item');
        }
        $this->authorize('update', $codeDetail);
        $description = $request->getDescription($codeDetail->CodeID, $codeDetailId);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($codeDetail, $description, $actor) {
                $codeDetail->update([
                    'Description' => $description,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($codeDetail)->event('update')->log('updated ' . $codeDetail->CodeID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create code detail :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('updated successfully', data: ['list' => $codeDetail->CodeID]);
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $codeDetailId): JsonResponse
    {
        $codeDetail = CodeDetail::query()->where('ID', $codeDetailId)->first();
        if (!$codeDetail instanceof CodeDetail) {
            return $this->errored('could not validate that  item');
        }
        $this->authorize('update', $codeDetail);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($codeDetail, $actor) {
                $codeDetail->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id
                ])->save();

                activity()->causedBy($actor)->performedOn($codeDetail)->event('delete')->log('deleted system code ' . $codeDetail->CodeID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create code detail :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('trashed successfully', data: ['list' => $codeDetail->CodeID]);
    }
}
