<?php

namespace App\Http\Controllers\ProductDev;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\ProductDevelopment;
use App\Services\ProductDevService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductDevelopmentCommentController extends Controller
{
    private const PAGINATION = 10;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $product_id): CommentCollection|JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        $this->authorize('view', $product);

        if ($request->has('CommentId')) {
            return new CommentCollection(Comment::query()->where('t_Comments.CommentType', Comment::getPrimaryKey())
                ->where('t_Comments.CommentTypeID', $request->get('CommentId'))->with('creator')->latest('t_Comments.Id')->paginate(self::PAGINATION));
        }

        return new CommentCollection($product->comments()->with('creator')->latest('t_Comments.Id')->paginate(self::PAGINATION));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $product_id): JsonResponse|CommentResource
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        $this->authorize('view', $product);

        $request->validate([
            'social_comment' => ['required', 'max:5000']
        ]);
        $parent = null;
        if ($request->has('CommentId')) {
            $cmt = $product->comments()->where('t_Comments.Id', $request->get('CommentId'))->first();
            $parent = ($cmt instanceof Comment) ? $cmt : null;
        }

        try {
            $comment = DB::transaction(static function () use ($parent, $request, $product) {
                return (new ProductDevService($product))->comment($request->get('social_comment'), $request->user(), $parent);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create product development comment ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('comment added', data: ['data' => new CommentResource($comment)]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $product_id, $comment_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('Product development not found', status: 404);
        }
        if (!(new ProductDevService($product))->commenting()) {
            return $this->errored('comments not enabled');
        }
        $this->authorize('view', $product);
        $comment = Comment::query()->where('t_Comments.Id', $comment_id)->first();
        if (!$comment instanceof Comment || $comment->trashed()) {
            return $this->errored('comment could have been trashed', status: 404);
        }

        //todo check if comment part of product.
        if ($comment->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $request->user()->Id
        ])->save()) {
            return $this->succeeded('comment trashed successfully', data: ['id' => $comment->Id]);
        }

        return $this->errored('unexpected error, try again later');
    }
}
