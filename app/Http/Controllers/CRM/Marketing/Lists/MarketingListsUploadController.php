<?php

namespace App\Http\Controllers\CRM\Marketing\Lists;

use App\Enums\MarketingListEnum;
use App\Events\Marketing\MarketingListUploadedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Core\CsvUploadRequest;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use App\Models\CRM\MarketingList;
use App\Services\Marketing\ListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketingListsUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Progress Bar
     */
    public function index(MarketingList $list): JsonResponse
    {
        $this->authorize('view', $list);
        if (is_array($list->Processing) && array_key_exists('done', $list->Processing) && array_key_exists('total', $list->Processing)) {
            $total = $list->Processing['total'];
            $done = $list->Processing['done'];
        } else {
            $total = 0;
            $done = 0;
        }

        return $this->succeeded('ok', data: [
                                             'progress' => (int) ($total > 0) ? (($done / $total) * 100) : 100,
                                             'done' => $done,
                                             'total' => (int) $total,
                                             'description' => 'Processing Data (' . number_format($done) . ' / ' . number_format($total) . ')',
                                            ]);
    }

    /**
     * Upload CSV for Processing.
     */
    public function store(CsvUploadRequest $request, MarketingList $list): JsonResponse
    {
        $this->authorize('update', $list);
        if ($list->Type->value !== MarketingListEnum::Static->value) {
            return $this->errored('only static lists can be uploaded.');
        }

        $type = $request->validate([
                                    'Type' => [
                                               'required',
                                               Rule::in([Client::getPrimaryKey(), Lead::getPrimaryKey()]),
                                              ],
                                   ], [
                                       'Type.required' => 'type is required',
                                       'Type.in' => 'type is invalid',
                                      ])['Type'];

        $service = (new ListService($list));
        if (! $service->canSource($type)) {
            throw ValidationException::withMessages([
                                                     'Type' => 'Type and List do not match only ' . $service->source(),
                                                    ]);
        }

        $file = $request->getFile();

        $data = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($data);

        if ($type === Client::getPrimaryKey()) {
            $requiredHeaders = ['MemberID'];
        } elseif ($type === Lead::getPrimaryKey()) {
            $requiredHeaders = [
                                'Phone',
                                'Email',
                               ];
        } else {
            return $this->errored('type is invalid');
        }

        if (array_diff($requiredHeaders, $headers)) {
            throw ValidationException::withMessages([
                                                     'file' => 'The provided CSV file is missing some required fields: ' . " " . implode(', ', array_diff($requiredHeaders, $headers)),
                                                    ]);
        }

        //move file
        $file_path = storage_path('app/temp/');
        $name = $list->slug . time() . '.csv';
        $file->move($file_path, $name);

        $list->update(['Processing' => ['done' => 0, 'total' => count($data)]]);

        event(new MarketingListUploadedEvent($list, $type, $file_path . $name, $request->user()));

        return $this->succeeded('File successfully uploaded, processing to start soon', route: route('marketing-list.show', [$list->slug]));
    }
}
