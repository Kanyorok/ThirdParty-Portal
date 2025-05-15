<?php

namespace App\Http\Controllers\Settings\Codes;

use App\Enums\LocalityTypeEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LocalityRequest;
use App\Models\Core\Locality;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Yajra\DataTables\DataTables;

class LocalityController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
        $this->authorizeResource(Locality::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        if (!in_array($request->_code, LocalityTypeEnum::values(), true)) {
            throw new RuntimeException('Invalid list requested');
        }

        return Datatables::of(Locality::query()->where('LocationType', $request->_code)->with(['in'])->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Locality $locality) {
                $source = ($locality->in instanceof Locality) ? route('locality.select2') . "?type=" . LocalityTypeEnum::County->value : "";

                return '<button type="button" class="btn btn-primary btn-sm  location-action-update" data-in_source="' . $source . '" data-info="' . $locality->ID . '~' . $locality->Name . '~' . $locality->in?->ID . '~' . $locality->in?->Name . '"><i class="fas fa-edit"></i> edit</button>
                         <button type="button" class="btn btn-danger btn-sm  location-action-trash" data-info="' . $locality->ID . '~' . $locality->Name . '"><i class="fas fa-trash"></i> trash</button>';
            })->editColumn('in.Name', function (Locality $locality) {
                return $locality->in?->Name;
            })->rawColumns(['action'])->make();
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(LocalityRequest $request): JsonResponse
    {
        $actor = $request->user();
        $name = $request->getPlaceName();
        $type = $request->getType();
        $located = $request->getLocatedIn();
        try {
            DB::transaction(static function () use ($name, $type, $located, $actor) {
                $locality = Locality::create([
                                              'Name'         => $name,
                                              'LocationType' => $type,
                                              'LocalityID'   => $located,
                                              'CreatedBy'    => $actor->Id,
                                              'ModifiedBy'   => $actor->Id,
                                             ]);
                activity()->causedBy($actor)->performedOn($locality->refresh())->event('create')->log('created locality ' . $locality->Name);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create location :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }


        return $this->succeeded('added successfully', data: ['list' => $request->_type]);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(LocalityRequest $request, Locality $locality): JsonResponse
    {
        $actor = $request->user();
        $name = $request->getPlaceName();
        $located = $request->getLocatedIn();
        try {
            DB::transaction(static function () use ($located, $locality, $actor, $name) {
                $locality->fill([
                                 'Name'       => $name,
                                 'LocalityID' => $located,
                                 'ModifiedBy' => $actor->Id,
                                ])->save();

                activity()->causedBy($actor)->performedOn($locality)->event('update')->log('Updated location ' . $locality->Name);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error update location ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }


        return $this->succeeded('updated successfully', data: ['list' => $locality->LocationType]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Locality $locality): JsonResponse
    {
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($locality, $actor) {
                $locality->forceFill([
                                      'DeletedOn' => now(),
                                      'DeletedBy' => $actor->Id,
                                     ])->save();

                activity()->causedBy($actor)->performedOn($locality)->event('delete')->log('delete location  ' . $locality->Name);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error delete location ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }


        return $this->succeeded('Trashed successfully', data: ['list' => $locality->LocationType]);
    }
}
