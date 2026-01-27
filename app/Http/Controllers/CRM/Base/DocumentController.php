<?php

namespace App\Http\Controllers\CRM\Base;

use App\Http\Controllers\Controller;
use App\Models\DMS\Image;
use App\Services\DMS\ImageService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except('edit');
    }

    /**
     * Appendable Html Content for Content
     * @throws AuthorizationException
     * @deprecated
     */
    public function show(Image $image): View
    {
        //   $this->authorize('view', $image->source); todo fix for emails here
        return view('crm.base.documents.show', compact('image'))
            ->with('service', (new ImageService($image)));
    }

    /**
     * Download Content
     * @throws AuthorizationException
     * @deprecated
     */
    public function edit(Request $request, Image $image)
    {
        $this->authorize('view', $image->source);

        activity()->causedBy($request->user())->performedOn($image->source)->event('download')->log('downloaded attached document : ' . $image->Name);

        return (Response(base64_decode($image->Image), 200))
            ->header('ContentType', $image->MIMEType)
            ->header('Content-Disposition', 'attachment; filename=' . $image->Name);
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     * @deprecated
     */
    public function destroy(Request $request, Image $image): JsonResponse
    {
        $this->authorize('delete', $image->source);

        $actor = $request->user();

        try {
            DB::transaction(static function () use ($image, $actor) {
                $image->forceFill([
                    'DeletedBy' => $actor->Id,
                    'DeletedOn' => now(),
                ])->save();
                activity()->causedBy($actor)->performedOn($image->source)->event('delete')->log('trashed attached document : ' . $image->Name);
            });
        } catch (Exception $e) {
            Log::error('Error removing attachment :  ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        } catch (Throwable $e) {
        }

        return $this->succeeded('attachment removed successfully');
    }
}
