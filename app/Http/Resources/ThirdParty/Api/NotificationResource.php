<?php

namespace App\Http\Resources\ThirdParty\Api;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        $payload = data_get($this->resource, 'data', []);
        $type = data_get($this->resource, 'type');
        $readAt = $this->formatDate(data_get($this->resource, 'read_at'));
        $createdAt = $this->formatDate(data_get($this->resource, 'created_at'));

        return [
            'id' => data_get($this->resource, 'id'),
            'type' => class_basename((string) $type),
            'title' => $payload['title'] ?? $payload['subject'] ?? null,
            'body' => $payload['body'] ?? $payload['message'] ?? null,
            'link' => $payload['link'] ?? $payload['url'] ?? null,
            'profileType' => $payload['profile_type'] ?? $payload['profileType'] ?? null,
            'isRead' => ! is_null($readAt),
            'readAt' => $readAt,
            'createdAt' => $createdAt,
            'data' => $payload,
        ];
    }

    private function formatDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->toDateTimeString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
