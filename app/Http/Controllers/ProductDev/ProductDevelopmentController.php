<?php

namespace App\Http\Controllers\ProductDev;

use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDev\UpdateProductDevelopmentRequest;
use App\Models\ProductDevelopment;
use App\Services\ProductDevService;
use App\Services\StaticListsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ProductDevelopmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
        // $this->authorizeResource(ProductDevelopment::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ProductDevelopment::class);
        if ($request->ajax()) {
            $actor = $request->user();
            $query = ProductDevelopment::query();
            //check if has write permission
            if (!$actor->can(PermissionEnum::ProductDevelopmentUpdate->value)) {
                $query->whereNotNull('CommentStart')->whereNull('CommentEnd');
            }
            if ($request->has('status') && $request->get('status') !== 'archived') {
                $query->whereNotNull(['ArchivedOn', 'ArchivedBy']);
            } else {
                $query->whereNull(['ArchivedOn', 'ArchivedBy']);
            }


            return Datatables::of($query->lock('WITH(NOLOCK)')->select('*')->with('stage')->withCount('comments'))->addIndexColumn()
                ->editColumn('ProductID', function ($product) {
                    return '<a href="' . route('product-development.show', $product->ProductID) . '">' . Str::upper($product->ProductID) . '</a>';
                })->editColumn('comments_count', function ($product) {
                    return number_format($product->comments_count);
                })->editColumn('CreatedOn', function (ProductDevelopment $product) {
                    return $product->CreatedOn->format('d M, Y h:i A');
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (ProductDevelopment $product) {
                        return route('product-development.show', $product->ProductID);
                    }
                ])->rawColumns(['ProductID'])->make();
        }


        return view('product-dev.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ProductDevelopment::class);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:250'],
            'TargetGroup' => ['required', 'string', 'max:250'],
            'Notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $product = DB::transaction(static function () use ($data, $request) {
                return ProductDevService::create($data['Name'], $data['TargetGroup'], $data['Notes'], $request->user())->product;
            });
        } catch (Exception $e) {
            Log::error('Error adding product dev: ' . $e->getMessage());
            return $this->errored('unexpected error adding, try again latter');
        }

        return $this->succeeded('product development added', route('product-development.show', [$product->ProductID]));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $product_id): RedirectResponse|View
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return redirect()->back()->with('fail', 'Product development not found');
        }
        $this->authorize('view', $product);
        $canUpdate = (auth()->user()->can(PermissionEnum::ProductDevelopmentUpdate->value) && (is_null($product->ArchivedBy) && is_null($product->ArchivedOn)));
        return view('product-dev.show', compact('product'))
            ->with('canUpdate', $canUpdate)
            ->with('canComment', (new ProductDevService($product))->commenting())
            ->with('stages', $canUpdate ? StaticListsService::getList(StaticListsService::ProductDevelopmentStages) : collect([]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductDevelopmentRequest $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid', status: 404);
        }

        $this->authorize('update', $product);

        try {
            $data = DB::transaction(static function () use ($product, $request) {
                $data = $request->getData();

                $data = $data->merge([
                    'ModifiedBy' => $request->user()->Id,
                ]);

                $product->fill($data->toArray())->save();

                if ($data->has('StageId')) {
                    $data = $data->put('StageId', $product->stage->Description);
                }
                if ($data->has('Income')) {
                    $data = $data->put('Income', Number::abbreviate($product->Income, 2));
                }
                if ($data->has('Revenue')) {
                    $data = $data->put('Revenue', Number::abbreviate($product->Revenue, 2));
                }
                return $data;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error update product : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('product updated successfully', data: ['fields' => $data->toArray()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid', status: 404);
        }
        $this->authorize('delete', $product);

        $user = $request->user();
        $product->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $user->Id,
        ])->save();

        activity()->causedBy($user)->performedOn($product)->event('delete')->log('Trashed Product Development ' . Str::upper($product->ProductID) . '.');

        return $this->succeeded('product trashed success', route: route('product-development.index'));
    }
}
