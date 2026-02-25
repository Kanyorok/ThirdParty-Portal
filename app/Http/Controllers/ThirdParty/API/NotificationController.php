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
        $unreadCount = $notifications->filter(fn ($item) => empty($item['read_at']))->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => $notifications->count(),
                'unread' => $unreadCount,
            ],
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
                'message' => 'Notification not found.',
            ], 404);
        }

        if ($type === 'email') {
            $record->forceFill([
                'ReadOn' => Carbon::now(),
                'ReadBy' => $user->Id,
            ])->save();
            $payload = $this->mapEmailNotification($record);
        } else {
            $this->markSmsAsRead($record, $user);
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

        $readOn = Carbon::now();

        $emailQuery = Email::query();
        $this->applyPartyFilter($emailQuery, $user);
        $updatedEmails = $emailQuery->update([
            'ReadOn' => $readOn,
            'ReadBy' => $user->Id,
        ]);

        $updatedSms = 0;
        $smsQuery = SMS::query();
        $this->applyPartyFilter($smsQuery, $user);
        $smsQuery->chunkById(200, function ($smsRecords) use ($user, $readOn, &$updatedSms) {
            foreach ($smsRecords as $sms) {
                $meta = $this->normalizeExtra($sms->Response);
                if (! empty($meta['read_at'])) {
                    continue;
                }

                $meta['read_at'] = $readOn->toIso8601String();
                $meta['read_by'] = $user->Id;

                $sms->forceFill([
                    'Response' => (object) $meta,
                ])->save();

                $updatedSms++;
            }
        }, 'Id');

        return response()->json([
            'success' => true,
            'message' => 'All notifications have been marked as read.',
            'updated' => [
                'emails' => $updatedEmails,
                'sms' => $updatedSms,
            ],
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
        $readAt = $this->parseDateValue($meta['read_at'] ?? null);

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
            'read_at' => $readAt,
            'created_at' => $this->parseDateValue($sms->CreatedOn ?? $sms->Dated),
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

        if (is_string($extra)) {
            $decoded = json_decode($extra, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function markSmsAsRead(SMS $sms, ThirdPartyUser $user): void
    {
        $meta = $this->normalizeExtra($sms->Response);
        $meta['read_at'] = Carbon::now()->toIso8601String();
        $meta['read_by'] = $user->Id;

        $sms->forceFill([
            'Response' => (object) $meta,
        ])->save();
    }

    private function parseDateValue($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
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
