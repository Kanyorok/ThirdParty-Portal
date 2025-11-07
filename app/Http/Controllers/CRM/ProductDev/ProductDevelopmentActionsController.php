<?php

namespace App\Http\Controllers\CRM\ProductDev;

use App\Events\ProductDev\ProductDevCommentingEvent;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Models\CRM\ProductDevelopment;
use App\Services\DMS\ImageService;
use App\Services\ProductDevService;
use App\Traits\Controller\ActivitiesTrait;
use App\Traits\Controller\WorkflowTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProductDevelopmentActionsController extends Controller
{
    use WorkflowTrait;
    use ActivitiesTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception|AuthorizationException
     */
    public function activity(string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        $this->authorize('view', $product);

        return $this->activities($product->userActivities(), ['causer']);
    }

    /**
     * @throws Exception|AuthorizationException
     */
    public function workflow(string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }

        $this->authorize('view', $product);

        return $this->workflows($product->workflows());
    }

    /**
     * @throws AuthorizationException
     */
    public function upload(UploadDocumentRequest $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }

        $this->authorize('update', $product);

        try {
            $document = DB::transaction(static function () use ($request, $product) {
                return (new ProductDevService($product))->document($request->file('file'), $request->user());
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error upload product development document : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('document uploaded successfully', data: [
                                                                         'html' => (new ImageService($document))->summaryList(),
                                                                        ]);
    }

    public function enableComment(Request $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        $this->authorize('update', $product);

        if (!is_null($product->CommentStart)) {
            return $this->errored('comments already enabled');
        }

        try {
            $product = DB::transaction(static function () use ($request, $product) {
                $product->fill([
                                'CommentStart' => now(),
                               ])->save();

                activity()->causedBy($request->user())->performedOn($product)->event('enabled')->log('Enabled commenting product development ' . Str::upper($product->ProductID) . '.');

                event(new ProductDevCommentingEvent($product, true));

                return $product;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error enable commenting product development : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('comments enabled', route: route('product-development.show', $product->ProductID));
    }

    public function disableComment(Request $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        $this->authorize('update', $product);

        if (is_null($product->CommentStart)) {
            return $this->errored('comments  not enabled');
        }

        if (!is_null($product->CommentEnd)) {
            return $this->errored('commenting is already ended');
        }

        try {
            $product = DB::transaction(static function () use ($request, $product) {
                $product->fill([
                                'CommentEnd' => now(),
                               ])->save();

                activity()->causedBy($request->user())->performedOn($product)->event('disabled')->log('Ended commenting on product development ' . Str::upper($product->ProductID) . '.');

                event(new ProductDevCommentingEvent($product, false));

                return $product;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error disable commenting product development : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('comments disabled', route: route('product-development.show', $product->ProductID));
    }


    /**
     * @throws AuthorizationException
     */
    public function submit(Request $request, string $product_id): JsonResponse
    {
        $actor = $request->user();
        $product = ProductDevelopment::where('ProductID', $product_id)->where('User_ID', $actor->Id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid or no permission', status: 404);
        }

        $this->authorize('update', $product);

        try {
            return DB::transaction(function () use ($product, $actor) {
                $product->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id,
                ])->save(['timestamps' => false]);

                activity()->causedBy($actor)->performedOn($product)->event('archived')->log('Archived product (' . $product->ProductID . ') in development .');
                return $this->succeeded('product submitted successfully.', route('product-development.index'));
            });
        } catch (Throwable $e) {
            Log::error('Error submitting product failed: ');
        }
        return $this->errored('unexpected error, try again later');
    }
}
