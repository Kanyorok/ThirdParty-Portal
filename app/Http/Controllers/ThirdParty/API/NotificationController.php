<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThirdParty\Api\NotificationResource;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\ThirdParty\ThirdPartyUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        $notifications = $this->collectNotifications($user);

        return response()->json([
            'success' => true,
            'notifications' => NotificationResource::collection($notifications),
        ]);
    }

    public function markAsRead(Request $request, string $type, string $notificationId): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        $type = Str::lower($type);
        if ($type === 'sms') {
            $record = SMS::where('SMSId', $notificationId)->first();
        } elseif ($type === 'email') {
            $record = Email::where('EmailID', $notificationId)->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported notification type.',
            ], 400);
        }

        if (! $record || ! $this->belongsTo($record, $user)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if ($type === 'email') {
            $record->forceFill([
                'ReadOn' => Carbon::now(),
                'ReadBy' => $user->Id,
            ])->save();
            $payload = $this->mapEmailNotification($record);
        } else {
            $payload = $this->mapSmsNotification($record);
        }

        return response()->json([
            'success' => true,
            'notification' => new NotificationResource((object) $payload),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveAuthenticatedUser($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        $query = Email::query();
        $this->applyPartyFilter($query, $user);
        $query->update([
            'ReadOn' => Carbon::now(),
            'ReadBy' => $user->Id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications have been marked as read.',
        ]);
    }

    private function collectNotifications(ThirdPartyUser $user): Collection
    {
        $items = collect();

        $smsQuery = SMS::query();
        $this->applyPartyFilter($smsQuery, $user);
        $smsRecords = $smsQuery->orderByDesc('CreatedOn')->limit(40)->get();
        foreach ($smsRecords as $sms) {
            $items->push($this->mapSmsNotification($sms));
        }

        $emailQuery = Email::query();
        $this->applyPartyFilter($emailQuery, $user);
        $emailRecords = $emailQuery->orderByDesc('CreatedOn')->limit(40)->get();
        foreach ($emailRecords as $email) {
            $items->push($this->mapEmailNotification($email));
        }

        return $items->sortByDesc('created_at')->values()->take(40);
    }

    private function applyPartyFilter($query, ThirdPartyUser $user): void
    {
        $query->where(function ($q) use ($user) {
            if ($user->ThirdPartyId) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('Party', 'ThirdParty')
                        ->where('PartyID', $user->ThirdPartyId);
                });

                $q->orWhere(function ($sub) use ($user) {
                    $sub->where('Party', 'ThirdPartyUser')
                        ->where('PartyID', $user->Id);
                });
            } else {
                $q->where('Party', 'ThirdPartyUser')
                    ->where('PartyID', $user->Id);
            }
        });
    }

    private function mapSmsNotification(SMS $sms): array
    {
        $meta = $this->normalizeExtra($sms->Response ?? $sms->Extra);

        return [
            'id' => $sms->SMSId,
            'type' => 'SMS',
            'data' => [
                'title' => $meta['title'] ?? 'SMS Notification',
                'body' => $sms->Content,
                'link' => $meta['link'] ?? null,
                'profile_type' => $meta['profile_type'] ?? $meta['profileType'] ?? null,
                'phone' => $sms->Phone,
                'status' => $sms->Status,
                'source' => 'sms',
                'extra' => $meta,
            ],
            'read_at' => null,
            'created_at' => $sms->CreatedOn ?? $sms->Dated,
        ];
    }

    private function mapEmailNotification(Email $email): array
    {
        $meta = $this->normalizeExtra($email->Extra);

        return [
            'id' => $email->EmailID,
            'type' => 'Email',
            'data' => [
                'title' => $email->Subject,
                'body' => $email->Body,
                'link' => $meta['link'] ?? null,
                'profile_type' => $meta['profile_type'] ?? $meta['profileType'] ?? null,
                'from' => $email->From,
                'to' => $email->To,
                'status' => $email->Status,
                'source' => 'email',
                'extra' => $meta,
            ],
            'read_at' => $email->ReadOn,
            'created_at' => $email->CreatedOn ?? $email->Dated,
        ];
    }

    private function normalizeExtra($extra): array
    {
        if (is_array($extra)) {
            return $extra;
        }

        if (is_object($extra)) {
            return (array) $extra;
        }

        return [];
    }

    private function resolveAuthenticatedUser(Request $request): ?ThirdPartyUser
    {
        $user = $request->user();

        return $user instanceof ThirdPartyUser ? $user : null;
    }

    private function belongsTo($record, ThirdPartyUser $user): bool
    {
        $party = $record->Party;
        $partyId = $record->PartyID;

        if (! $party || ! $partyId) {
            return false;
        }

        if ($party === 'ThirdParty') {
            return (string) $partyId === (string) $user->ThirdPartyId;
        }

        if ($party === 'ThirdPartyUser') {
            return (string) $partyId === (string) $user->Id;
        }

        return false;
    }
}
