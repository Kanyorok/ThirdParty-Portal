<?php

namespace App\Services;

use App\Models\Finance\Cheque;
use App\Models\Finance\ChequeBook;

class ChequeValidationService
{
    /**
     * Status transition matrix defining valid state changes
     * Based on ERP workflow rules
     */
    private const VALID_TRANSITIONS = [
        'A' => ['I', 'S', 'V'],           // Available → Issued, Spoiled, Void
        'I' => ['U', 'V'],                // Issued → Used, Void
        'U' => ['P', 'PD'],               // Used → Posted, PDC
        'P' => ['C', 'B', 'PR'],          // Posted → Cleared, Bounced, Presented
        'PD' => ['P'],                    // PDC → Posted (on due date)
        'PR' => ['C', 'B'],               // Presented → Cleared, Bounced
        // Terminal statuses (no valid transitions)
        'C' => [],                        // Cleared (terminal)
        'B' => [],                        // Bounced (terminal)
        'V' => [],                        // Void (terminal)
        'S' => [],                        // Spoiled (terminal)
    ];

    /**
     * Statuses that impact GL posting
     */
    private const GL_IMPACTING_STATUSES = ['P', 'C', 'B'];

    /**
     * Terminal statuses that cannot be changed
     */
    private const TERMINAL_STATUSES = ['C', 'B', 'V', 'S'];

    /**
     * Validate if a status transition is allowed
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return bool
     */
    public function validateStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // Cannot transition from terminal status
        if (in_array($currentStatus, self::TERMINAL_STATUSES)) {
            return false;
        }

        // Check if transition exists in matrix
        if (! isset(self::VALID_TRANSITIONS[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::VALID_TRANSITIONS[$currentStatus]);
    }

    /**
     * Get all valid next statuses for a given current status
     *
     * @param string $currentStatus
     * @return array
     */
    public function getValidNextStatuses(string $currentStatus): array
    {
        return self::VALID_TRANSITIONS[$currentStatus] ?? [];
    }

    /**
     * Check if a cheque book can be deleted
     *
     * @param ChequeBook $book
     * @return array ['canDelete' => bool, 'reason' => string|null]
     */
    public function canDeleteBook(ChequeBook $book): array
    {
        // Rule: Cannot delete if any leaf has been used
        $hasUsedLeaves = $book->leaves()
            ->where('Status', '!=', 'A')
            ->exists();

        if ($hasUsedLeaves) {
            return [
                'canDelete' => false,
                'reason' => 'Cannot delete cheque book: One or more leaves have been used or are not available.',
            ];
        }

        return ['canDelete' => true, 'reason' => null];
    }

    /**
     * Check for cheque number overlaps
     *
     * @param int $bankAccountId
     * @param int $startNumber
     * @param int $endNumber
     * @param int|null $excludeBookId
     * @return bool
     */
    public function hasNumberingOverlap(
        int $bankAccountId,
        int $startNumber,
        int $endNumber,
        ?int $excludeBookId = null
    ): bool {
        $query = ChequeBook::where('BankAccountID', $bankAccountId)
            ->where(function ($q) use ($startNumber, $endNumber) {
                // Check if ranges overlap
                $q->whereBetween('StartNumber', [$startNumber, $endNumber])
                  ->orWhereBetween('EndNumber', [$startNumber, $endNumber])
                  ->orWhere(function ($q2) use ($startNumber, $endNumber) {
                      // Or if the new range contains an existing range
                      $q2->where('StartNumber', '>=', $startNumber)
                         ->where('EndNumber', '<=', $endNumber);
                  });
            });

        if ($excludeBookId) {
            $query->where('ChequeBookID', '!=', $excludeBookId);
        }

        return $query->exists();
    }

    /**
     * Check if a status change requires GL posting
     *
     * @param string $status
     * @return bool
     */
    public function requiresGLPosting(string $status): bool
    {
        return in_array($status, self::GL_IMPACTING_STATUSES);
    }

    /**
     * Check if a status is terminal (cannot be changed)
     *
     * @param string $status
     * @return bool
     */
    public function isTerminalStatus(string $status): bool
    {
        return in_array($status, self::TERMINAL_STATUSES);
    }

    /**
     * Validate cheque book size
     *
     * @param int $size
     * @return array ['isValid' => bool, 'reason' => string|null]
     */
    public function validateBookSize(int $size): array
    {
        $validSizes = [25, 50, 100];

        if (! in_array($size, $validSizes)) {
            return [
                'isValid' => false,
                'reason' => 'Invalid book size. Allowed sizes are: 25, 50, or 100 leaves.',
            ];
        }

        return ['isValid' => true, 'reason' => null];
    }

    /**
     * Validate if cheque can be posted
     * Rule: Must be Used (U) or PDC (PD) before posting
     *
     * @param string $currentStatus
     * @return array ['canPost' => bool, 'reason' => string|null]
     */
    public function canPost(string $currentStatus): array
    {
        if (! in_array($currentStatus, ['U', 'PD'])) {
            return [
                'canPost' => false,
                'reason' => 'Cheque must be in "Used" or "Post-Dated" status before posting.',
            ];
        }

        return ['canPost' => true, 'reason' => null];
    }

    /**
     * Check if cheque can be cleared or bounced
     * Rule: Must be Posted (P) or Presented (PR)
     *
     * @param string $currentStatus
     * @return array ['canClearOrBounce' => bool, 'reason' => string|null]
     */
    public function canClearOrBounce(string $currentStatus): array
    {
        if (! in_array($currentStatus, ['P', 'PR'])) {
            return [
                'canClearOrBounce' => false,
                'reason' => 'Cheque must be in "Posted" or "Presented" status before clearing or bouncing.',
            ];
        }

        return ['canClearOrBounce' => true, 'reason' => null];
    }

    /**
     * Get human-readable status name
     *
     * @param string $statusCode
     * @return string
     */
    public function getStatusName(string $statusCode): string
    {
        $statusNames = [
            'A' => 'Available',
            'I' => 'Issued',
            'U' => 'Used',
            'P' => 'Posted',
            'PD' => 'Post-Dated Cheque',
            'PR' => 'Presented',
            'C' => 'Cleared',
            'B' => 'Bounced',
            'V' => 'Void',
            'S' => 'Spoiled',
        ];

        return $statusNames[$statusCode] ?? $statusCode;
    }

    /**
     * Validate complete cheque lifecycle rules
     *
     * @param Cheque $cheque
     * @param string $newStatus
     * @return array ['isValid' => bool, 'errors' => array]
     */
    public function validateChequeStatusChange(Cheque $cheque, string $newStatus): array
    {
        $errors = [];

        // Check if transition is valid
        if (! $this->validateStatusTransition($cheque->Status, $newStatus)) {
            $errors[] = sprintf(
                'Invalid status transition from %s to %s',
                $this->getStatusName($cheque->Status),
                $this->getStatusName($newStatus)
            );
        }

        // Special rule: Cannot post without being Used or PDC
        if ($newStatus === 'P') {
            $canPost = $this->canPost($cheque->Status);
            if (! $canPost['canPost']) {
                $errors[] = $canPost['reason'];
            }
        }

        // Special rule: Cannot clear/bounce without being Posted or Presented
        if (in_array($newStatus, ['C', 'B'])) {
            $canClearBounce = $this->canClearOrBounce($cheque->Status);
            if (! $canClearBounce['canClearOrBounce']) {
                $errors[] = $canClearBounce['reason'];
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
