<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\CommitteeRoleHistory;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\TenderCommitteeMember;

class TenderAssignRoleController extends Controller
{
    public function index()
    {
        // Fetch all role change history with member info
        $roleHistory = CommitteeRoleHistory::query()
            ->orderByDesc('ChangedOn')
            ->get()
            ->map(function ($history) {
                // Resolve the member based on type
                $member = null;
                $reference = 'N/A';

                if ($history->MemberType === 'tender') {
                    $member = TenderCommitteeMember::with([
                        'user' => fn ($q) => $q->withTrashed(),
                        'user.employee',
                        'userByEmployee' => fn ($q) => $q->withTrashed(),
                        'userByEmployee.employee',
                        'committee.tender',
                    ])->find($history->MemberID);

                    if ($member) {
                        $reference = optional(optional($member->committee)->tender)->TenderNo ?? 'N/A';
                    }
                } elseif ($history->MemberType === 'rfq') {
                    $member = RFQCommitteeMember::with([
                        'user' => fn ($q) => $q->withTrashed(),
                        'user.employee',
                        'userByEmployee' => fn ($q) => $q->withTrashed(),
                        'userByEmployee.employee',
                        'committee.rfq',
                    ])->find($history->MemberID);

                    if ($member) {
                        $reference = optional(optional($member->committee)->rfq)->RFQNumber ?? 'N/A';
                    }
                }

                $resolvedUser = $member ? ($member->user ?? $member->userByEmployee) : null;
                $resolvedEmployee = $resolvedUser?->employee;
                $memberName = $resolvedEmployee?->full_name ?? $resolvedUser?->Name ?? 'N/A';

                $history->member_name = $memberName;
                $history->reference   = $reference;

                return $history;
            });

        return view('procurement.tendering.bidopeningandevaluation.committeeroles.index', compact('roleHistory'));
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.committeeroles.create');
    }
}
