<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemMasterListRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemSubCategories;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ItemMasterListController extends Controller
{

    public function __construct() {
        $this->authorizeResource(ItemMasterList::class);
        $this->middleware('ajax')->only('store');
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of(ItemMasterList::query()->select('*'))->addIndexColumn()
                ->addColumn('Action', function (ItemMasterList $item) {
                    return
                        '<a href="' . route('itemmasterlist.show', $item->Id) . '">View</a> | ' .
                        '<a href="' . route('itemmasterlist.edit', $item->Id) . '">Edit</a> | ' .
                        '<a href="#" onclick="confirmDelete(\'' . $item->Id . '\')">Delete</a>';
                })
                ->rawColumns(['Action'])
                ->make(true);
        }

        return view('inventory.itemmaster.itemmasterlist.index');
    }


public function create()
{
    $categories = ItemCategories::all();
    $subcategories = ItemSubCategories::all();
    return view('inventory.itemmaster.itemmasterlist.create', compact('categories', 'subcategories'));
}


    /**
     * @throws ValidationException
     */
    public function store(ItemMasterListRequest $request): JsonResponse
 {
     $category = $request->getCategory();
     $subcategory = $request->getSubcategory();
     $actor = $request->user();
    try {
        DB::transaction(function () use ($request, $category, $subcategory, $actor) {
            //Used wit
            $itm = ItemMasterList::create([
                'ItemCode' => $request->getItemCode(),
                'BarCode' => $request->validated('BarCode'),
                'ItemName' => $request->string('ItemName')->trim()->toString(),
                'ItemType' => $request->validated('ItemType'),
                'Category' => $category->id,
                'SubCategory' => $subcategory->Id,
                'UOM' => $request->validated('UOM'),
                'InventoryType' => $request->validated('InventoryType'),
               // 'ImageUpload' ,/
                'ItemDescription' => $request->validated('ItemDescription'),
                //'DocumentUpload'
                'CreatedBy' => $actor->id,
                'UpdatedBy' => $actor->Id,
            ]);

            //Used in Logs
            activity()->causedBy($actor)->performedOn($itm)->event('create')->log('Created Item ');
        });
    }catch (\Exception $exception){
       return $this->errored($exception->getMessage());
    }

    return $this->succeeded('Item Master List created!', route('itemmaster.index'));
}

    public function show($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

    public function edit($Id)
    {
        $item = ItemMasterList::where('Id', $Id)->firstOrFail();
        $categories = ItemCategories::all();
        $subcategories = ItemCategories::all();
        return view('inventory.itemmaster.itemmasterlist.edit', compact('item', 'categories', 'subcategories'));
    }

public function update(Request $request, $Id)
{
    $item = ItemMasterList::where('Id', $Id)->firstOrFail();

    $validatedData = $request->validate([
        'ItemCode'      => 'required',
        'BarCode'       => 'required',
        'ItemName'      => 'required',
        'ItemType'      => 'required',
        'Category'      => 'required|exists:t_ItemCategories,id',
        'SubCategory'   => 'required|exists:t_ItemSubCategories,Id',
        'UOM'           => 'required',
        'InventoryType' => 'required',
        'ImageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'ItemDescription' => 'nullable',
        'DocumentUpload' => 'nullable'
    ]);
  if ($request->hasFile('ImageUpload')) {
        $path = $request->file('ImageUpload')->store('items', 'public');
        $item->ImageUpload = $path;
    }

    $item->update($validatedData);

    return redirect()->route('itemmaster.index')->with('success', '✅ Changes saved successfully!');
}


    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $item->delete();

        return redirect()->route('itemmaster.index')->with('success', 'Item deleted successfully!');
    }
}
