<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\ContractPenaltyRule;

class ContractInvoiceEligibilityService
{
    public function refreshInvoiceHoldStatus(FinanceInvoiceEntry $invoice): array
    {
        if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) !== 'CONTRACT') {
            return $this->asResult(
                true,
                null,
                (float) ($invoice->PenaltySuggestedAmount ?? 0),
                (string) ($invoice->MilestoneEligibilityStatus ?? 'Eligible'),
                false
            );
        }

        // Respect explicit manual waiver.
        if (
            strtolower((string) ($invoice->MilestoneEligibilityStatus ?? '')) === 'waived'
            && !(bool) $invoice->IsOnHold
        ) {
            return $this->asResult(
                true,
                $invoice->HoldReason,
                (float) ($invoice->PenaltySuggestedAmount ?? 0),
                'Waived',
                false
            );
        }

        $contractType = strtolower((string) ($invoice->ContractSourceType ?? ''));
        $contractId = (int) ($invoice->ContractSourceID ?? 0);

        if ($contractType === '' || $contractId <= 0) {
            return $this->asResult(
                !(bool) $invoice->IsOnHold,
                $invoice->HoldReason,
                (float) ($invoice->PenaltySuggestedAmount ?? 0),
                (string) ($invoice->MilestoneEligibilityStatus ?? 'Pending'),
                false
            );
        }

        $invoice->loadMissing([
            'milestoneAllocations.milestone.checklistItems',
        ]);

        $milestones = $invoice->milestoneAllocations
            ->map(fn ($allocation) => $allocation->milestone)
            ->filter()
            ->unique('Id')
            ->values();

        if ($milestones->isEmpty()) {
            return $this->asResult(
                !(bool) $invoice->IsOnHold,
                $invoice->HoldReason,
                (float) ($invoice->PenaltySuggestedAmount ?? 0),
                (string) ($invoice->MilestoneEligibilityStatus ?? 'Pending'),
                false
            );
        }

        $evaluated = $this->evaluate($contractType, $contractId, $milestones, (float) ($invoice->InvoiceAmount ?? 0));

        $newStatus = $evaluated['eligible'] ? 'Eligible' : 'Pending';
        $newHold = !$evaluated['eligible'];
        $newReason = $evaluated['eligible'] ? null : $evaluated['hold_reason'];
        $newPenalty = (float) $evaluated['penalty_suggested_amount'];

        $dirty = false;
        $changes = [];
        if ((string) ($invoice->MilestoneEligibilityStatus ?? '') !== $newStatus) {
            $changes['MilestoneEligibilityStatus'] = $newStatus;
            $dirty = true;
        }
        if ((bool) $invoice->IsOnHold !== $newHold) {
            $changes['IsOnHold'] = $newHold;
            $dirty = true;
        }
        if ((string) ($invoice->HoldReason ?? '') !== (string) ($newReason ?? '')) {
            $changes['HoldReason'] = $newReason;
            $dirty = true;
        }
        if ((float) ($invoice->PenaltySuggestedAmount ?? 0) !== $newPenalty) {
            $changes['PenaltySuggestedAmount'] = $newPenalty;
            $dirty = true;
        }

        if ($dirty) {
            $changes['HoldSetBy'] = $newHold ? ($invoice->HoldSetBy ?: auth()->id()) : null;
            $changes['HoldSetOn'] = $newHold ? ($invoice->HoldSetOn ?: now()) : null;
            $invoice->update($changes);
            $invoice->refresh();
        }

        return $this->asResult(
            !$newHold,
            $newReason,
            $newPenalty,
            $newStatus,
            $dirty
        );
    }

    private function evaluate(string $contractType, int $contractId, $milestones, float $invoiceAmount): array
    {
        $reasons = [];
        $eligible = true;
        $penaltySuggested = 0.0;
        $today = now()->startOfDay();

        $contractRule = ContractPenaltyRule::where('ContractSourceType', $contractType)
            ->where('ContractSourceID', $contractId)
            ->whereNull('MilestoneID')
            ->where('IsActive', true)
            ->latest('Id')
            ->first();

        foreach ($milestones as $milestone) {
            $requiredTotal = $milestone->checklistItems->where('Required', true)->count();
            $requiredDone = $milestone->checklistItems->where('Required', true)->where('IsFulfilled', true)->count();
            $isAccepted = in_array($milestone->Status, ['Accepted', 'Waived'], true);
            $isWaived = $milestone->Status === 'Waived';

            if (!$isAccepted || (!$isWaived && $requiredTotal !== $requiredDone)) {
                $eligible = false;
                $reasons[] = "M{$milestone->MilestoneNo} - {$milestone->Title} not accepted/complete.";
            }

            $rule = ContractPenaltyRule::where('ContractSourceType', $contractType)
                ->where('ContractSourceID', $contractId)
                ->where('MilestoneID', $milestone->Id)
                ->where('IsActive', true)
                ->latest('Id')
                ->first() ?: $contractRule;

            if ($rule && $milestone->PlannedDueDate) {
                $graceDays = (int) ($rule->GraceDays ?? 0);
                $dueDate = \Carbon\Carbon::parse($milestone->PlannedDueDate)->addDays($graceDays)->startOfDay();
                if ($today->greaterThan($dueDate) && !$isAccepted) {
                    $delayDays = $dueDate->diffInDays($today);
                    $penaltySuggested += $this->computeMilestonePenalty($rule, $delayDays, $invoiceAmount);
                }
            }
        }

        return [
            'eligible' => $eligible,
            'hold_reason' => $eligible ? null : implode(' ', $reasons),
            'penalty_suggested_amount' => round($penaltySuggested, 2),
        ];
    }

    private function computeMilestonePenalty(ContractPenaltyRule $rule, int $delayDays, float $invoiceAmount): float
    {
        $base = 0.0;
        if ($rule->PenaltyType === 'PER_DAY_DELAY') {
            $base = ((float) $rule->Rate) * $delayDays;
        } elseif ($rule->PenaltyType === 'PERCENT') {
            $base = $invoiceAmount * (((float) $rule->Rate) / 100);
        } else {
            $base = (float) ($rule->Rate ?? 0);
        }

        if (!empty($rule->CapAmount)) {
            $base = min($base, (float) $rule->CapAmount);
        }
        if (!empty($rule->CapPercent)) {
            $capPercentAmount = $invoiceAmount * (((float) $rule->CapPercent) / 100);
            $base = min($base, $capPercentAmount);
        }

        return max(0, $base);
    }

    private function asResult(bool $eligible, ?string $reason, float $penalty, string $status, bool $refreshed): array
    {
        return [
            'eligible' => $eligible,
            'hold_reason' => $reason,
            'penalty_suggested_amount' => round($penalty, 2),
            'status' => $status,
            'refreshed' => $refreshed,
        ];
    }
}

