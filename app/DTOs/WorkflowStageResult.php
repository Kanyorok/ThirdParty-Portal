<?php

namespace App\DTOs;

class WorkflowStageResult
{
    public string $message;
    public ?int $newStageId;
    public ?int $permissionId;

    public function __construct(string $message, ?int $newStageId = null, ?int $permissionId = null)
    {
        $this->message = $message;
        $this->newStageId = $newStageId;
        $this->permissionId = $permissionId;
    }

    public static function fromDatabaseResult(?object $result): self
    {
        if (! $result) {
            return new self('Unknown error occurred');
        }

        return new self(
            message: $result->Message ?? 'Unknown message',
            newStageId: $result->NewStageID ?? null,
            permissionId: $result->PermissionID ?? null,
        );
    }

    public function isError(): bool
    {
        return str_contains(strtolower($this->message), 'error');
    }
}
