<?php

namespace App\Listeners\Marketing;

use App\Enums\Core\ExtensionsEnum;
use App\Events\Marketing\MarketingListUploadedEvent;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\MarketingList;
use App\Services\HRM\UserService;
use App\Services\Marketing\ListService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Shuchkin\SimpleXLSXGen;
use Throwable;

class MarketingListProcessUploadListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(MarketingListUploadedEvent $event): void
    {
        if (! file_exists($event->file)) {
            $this->_completeProcessing($event->list, $event->actor, [], 0, 'Invalid or Unsupported upload file');
        }

        if ($event->Type === Client::getPrimaryKey()) {
            $this->_processClients($event->list, $event->file, $event->actor);
            unlink($event->file);

            return;
        }

        $this->_completeProcessing($event->list, $event->actor, array_map('str_getcsv', file($event->file)), 0, 'Invalid or Unsupported upload file');
        unlink($event->file);
    }

    private function _completeProcessing(MarketingList $list, User $actor, array $failed, int $successRate, string $notes): void
    {
        $list->update(['Processing' => null]);
        Log::info($notes . ' -> Failed : ' . count($failed) . ' -> Success Rate : ' . $successRate . '%');
        $service = (new UserService($actor))->sendEmail(
            subject: 'Upload Processing Complete',
            body: '<div><p>Dear ' . $actor->Name . ',</p>
                <p>The upload processing for the marketing list <a href="' . route('marketing-list.show', [$list->slug]) . '"><strong>' . $list->Label . '</strong></a> has been completed.</p>
                <p>Success Rate: <strong>' . $successRate . '%</strong></p>
                <p>Notes: ' . $notes . '</p>
                <p>More details can be found <a href="' . route('marketing-list.show', [$list->slug]) . '">here</a>.</p>
                </div>',
            immediate: null
        );

        if (count($failed) > 0) {
            try {
                $file = storage_path('app/temp/' . $list->slug . time() . '.xlsx');
                SimpleXLSXGen::fromArray($failed, "Failed Import")->saveAs($file);
                $service?->addAttachmentContent(file_get_contents($file), ExtensionsEnum::Xlsx->getMimeType(), $list->Label . ' Failed ' . now()->format('d M Y H:i') . '.xlsx', $actor);
                unlink($file);
            } catch (Exception | Throwable) {
            }
        }

        $service?->send(true);
    }

    private function _processClients(MarketingList $list, string $file, User $actor): void
    {

        $failed = collect();
        $data = array_map('str_getcsv', file($file));
        $headers = array_shift($data);
        $requiredHeaders = ['MemberID'];
        if (array_diff($requiredHeaders, $headers)) {
            $this->_completeProcessing($list, $actor, $data, 0, 'The provided CSV file is missing some required fields: ' . " " . implode(', ', array_diff($requiredHeaders, $headers)));

            return;
        }
        $total = count($data);
        $index = 0;
        $success = 0;
        $service = (new ListService($list));
        $ClientIDs = collect();
        foreach ($data as $row) {
            if ($ClientIDs->count() > 190) {
                $service->addClients($ClientIDs->toArray(), $actor);
                $ClientIDs = collect();
                $list->update(['Processing' => ['done' => $index, 'total' => $total]]);
            }
            $index++;
            $userData = array_combine($headers, $row);
            $ClientID = Str::padLeft($userData['MemberID'], 7, '0');
            if (Client::query()->where('ClientID', $ClientID)->exists()) {
                $ClientIDs->add($ClientID);
                $success++;

                continue;
            }
            $failed->add($ClientID);
            $list->update(['Processing' => ['done' => $index, 'total' => $total]]);
        }
        if ($ClientIDs->isNotEmpty()) {
            $service->addClients($ClientIDs->toArray(), $actor);
            $list->update(['Processing' => ['done' => $index, 'total' => $total]]);
        }
        $this->_completeProcessing($list, $actor, $failed->toArray(), ($success / $total) * 100, 'Successfully processed');
    }
}
