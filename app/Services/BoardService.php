<?php

namespace App\Services;

use App\Enums\EmailPriorityEnum;
use App\Models\Auth\User;
use App\Models\Communication\BulkNotification;
use App\Models\ThirdParies\Board;

class BoardService
{
    public function __construct(public Board $board)
    {
    }

    public function sendMessage(string $message, User $actor, bool $immediate = false, BulkNotification $bulkNotification = null): static
    {
        $service = SMSService::createBoard($this->board, $message, $actor);
        if ($bulkNotification instanceof BulkNotification) {
            $service->setBulk($bulkNotification);
        }
        $service->send($immediate);

        return $this;
    }

    public function sendEmail(string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = null): ?CRMEmailService
    {
        if (!filter_var($this->board->Email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        return CRMEmailService::createBoard($this->board, $subject, $body, $actor, $cc, $priorityEnum ?? EmailPriorityEnum::Normal);
    }
}
