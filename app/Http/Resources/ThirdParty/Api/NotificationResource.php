<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        $payload = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'title' => $payload['title'] ?? $payload['subject'] ?? null,
            'body' => $payload['body'] ?? $payload['message'] ?? null,
            'link' => $payload['link'] ?? $payload['url'] ?? null,
            'profileType' => $payload['profile_type'] ?? $payload['profileType'] ?? null,
            'isRead' => ! is_null($this->read_at),
            'readAt' => $this->read_at?->toDateTimeString(),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'data' => $payload,
        ];
    }
}
