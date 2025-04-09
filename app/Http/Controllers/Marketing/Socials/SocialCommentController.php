<?php

namespace App\Http\Controllers\Marketing\Socials;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Social;
use App\Services\SocialMediaService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SocialCommentController extends Controller
{
    private const int PAGINATION = 10;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws AuthorizationException
     */
    public function index(Request $request, Social $social): CommentCollection
    {
        $this->authorize('view', $social);
        if ($request->has('CommentId')) {
            return new CommentCollection(Comment::query()->where('t_Comments.CommentType', Comment::getPrimaryKey())
                ->where('t_Comments.CommentTypeID', $request->get('CommentId'))->with('creator')->latest('t_Comments.Id')->paginate(self::PAGINATION));
        }

        return new CommentCollection($social->comments()->with('creator')->latest('t_Comments.Id')->paginate(self::PAGINATION));
    }


    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(Request $request, Social $social): JsonResponse|CommentResource
    {
        $this->authorize('create', Social::class);
        $request->validate([
            'social_comment' => ['required', 'max:5000']
        ]);
        $parent = null;
        if ($request->has('CommentId')) {
            $cmt = $social->comments()->where('t_Comments.Id', $request->get('CommentId'))->first();
            $parent = ($cmt instanceof Comment) ? $cmt : null;
        }

        try {
            $comment = DB::transaction(static function () use ($parent, $request, $social) {
                return (new SocialMediaService($social))->comment($request->get('social_comment'), $request->user(), $parent);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create social comment ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('comment added', data: ['data' => new CommentResource($comment)]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Social $social, $comment_id): JsonResponse
    {
        $this->authorize('delete', Social::class);
        $comment = $social->comments()->where('t_Comments.Id', $comment_id)->first();

        /* if (!$comment instanceof Comment) {
             return $this->errored('comment not found');
         }

         if ($comment->forceFill([
             'DeletedOn' => now(),
             'DeletedBy' => $request->user()->Id
         ])->save()) {
             return $this->succeeded('comment trashed successfully', data: ['id' => $comment_id]);
         }*/

        return $this->errored('unexpected error, try again later');
    }
}
