<?php

namespace App\Http\Controllers\CRM\Marketing\Competitor;

use App\Enums\ItemTypeEnum;
use App\Enums\TonalityEnum;
use App\Http\Controllers\Controller;
use App\Models\CRM\DescriptionItem;
use App\Models\ThirdParies\Competitor;
use ErrorException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\DataTables;

class CompetitorItemsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('view', $competitor);
        if (! in_array($request->_type, ItemTypeEnum::keys(), true)) {
            throw new RuntimeException('Unknown item type');
        }

        return Datatables::of($competitor->items()->where('ItemType', ItemTypeEnum::valueFromName($request->_type)->value)->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (DescriptionItem $item) {
                return '<button type="button" class="btn btn-danger btn-sm trash-item-modal" data-info="' . $item->Id . '~' . $item->ItemType->name . '"><i class="fas fa-trash"></i> trash</button>';
            })->make();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('view', $competitor);
        if (! in_array($request->_type, ItemTypeEnum::keys(), true)) {
            throw new RuntimeException('Unknown item type');
        }

        $request->validate([
                            'ItemDescription' => [
                                                  'required',
                                                  'string',
                                                 ],
                           ], ['ItemDescription.required' => 'Description is required.']);

        try {
            DescriptionItem::create([
                                     "Item" => Competitor::getPrimaryKey(),
                                     "ItemID" => $competitor->CompetitorID,
                                     "Description" => $request->ItemDescription,
                                     "ItemType" => ItemTypeEnum::valueFromName($request->_type)->value,
                                     "Tonality" => TonalityEnum::Neutral->value,
                                     'CreatedBy' => $request->user()->Id,
                                     'ModifiedBy' => $request->user()->Id,
                                    ]);
        } catch (ErrorException) {
            return $this->errored('could not save try again latter');
        }

        return $this->succeeded('added successfully', data: ['list' => $request->_type]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Competitor $competitor, $itemID): JsonResponse
    {
        $this->authorize('view', $competitor);
        $item = $competitor->items()->where('Id', $itemID)->lock('WITH(NOLOCK)')->firstOrFail();
        $item->forceFill([
                          'DeletedBy' => $request->user()->Id,
                          'DeletedOn' => now(),
                         ])->save();

        return $this->succeeded('item trashed successfully', data: ['list' => $item->ItemType->name]);
    }
}
