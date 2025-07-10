<?php

namespace App\Http\Controllers\CRM\Board;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Models\CRM\Meeting;
use App\Models\ThirdParies\Board;
use App\Services\DMS\ImageService;
use App\Services\MeetingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BoardMeetingActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    public function upload(UploadDocumentRequest $request, string $meetingId): JsonResponse
    {
        $this->authorize('meeting', Board::class);
        $meeting = Meeting::query()->where('t_Meetings.Type', Board::getPrimaryKey())->where('t_Meetings.MeetingID', $meetingId)->lock('WITH(NOLOCK)')->first();
        if (!$meeting instanceof Meeting) {
            return $this->errored('meeting not found');
        }

        try {
            $document = DB::transaction(static function () use ($request, $meeting) {
                return (new MeetingService($meeting))->document($request->file('file'), $request->user());
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable|Exception $e) {
            Log::error('Error upload meeting document : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('document uploaded successfully', data: [
                                                                         'html' => (new ImageService($document))->summaryList(),
                                                                        ]);
    }
}
