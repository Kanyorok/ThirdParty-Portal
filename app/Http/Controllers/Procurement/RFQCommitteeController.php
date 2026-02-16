<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RFQCommitteeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'committeeType' => 'required|in:rfq',
            'referenceId' => 'required|integer|exists:t_RFQ,Id',
            'appointmentDate' => 'required|date',
            'committeeMembers' => 'required|array|min:1',
            'committeeMembers.*' => 'required|integer',
        ]);

        [$resolvedUserIds, $unresolvedIds] = $this->resolveCommitteeUserIds((array) $request->committeeMembers);
        if ($unresolvedIds->isNotEmpty()) {
            $missing = $this->formatMissingEmployeeNames($unresolvedIds);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Some selected members do not have active user accounts: ' . $missing);
        }

        if ($resolvedUserIds->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid committee members were selected.');
        }

        $referenceId = (int) $request->referenceId;
        $hasActiveCommittee = RFQCommittee::query()
            ->where('RFQID', $referenceId)
            ->where('IsActive', true)
            ->exists();

        if ($hasActiveCommittee) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An active committee already exists for this RFQ.');
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = Carbon::now();
            $committeeName = 'RFQ Committee for #' . $referenceId;

            $committee = RFQCommittee::create([
                'RFQID' => $referenceId,
                'CommitteeName' => $committeeName,
                'AppointmentDate' => $request->appointmentDate,
                'IsActive' => true,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
            ]);
            $committeeKey = (int) ($committee->getKey() ?? 0);

            foreach ($resolvedUserIds as $resolvedUserId) {
                $this->addOrRestoreRfqMember(
                    $committeeKey,
                    $referenceId,
                    (int) $resolvedUserId,
                    (int) $userId,
                    $now,
                    'Member'
                );
            }

            DB::commit();

            activity()
                ->performedOn($committee)
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => 'rfq',
                    'referenceId' => $referenceId,
                    'member_count' => $resolvedUserIds->count(),
                ])
                ->log('RFQ committee and members appointed successfully.');

            return redirect()->back()->with('success', 'RFQ committee successfully appointed.');
        } catch (\Throwable $th) {
            DB::rollBack();

            $activity = activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => 'rfq',
                    'referenceId' => $request->referenceId,
                ]);

            if (isset($committee)) {
                $activity->performedOn($committee);
            }

            $activity->log('Failed to appoint RFQ committee: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to appoint RFQ committee: ' . $th->getMessage());
        }
    }

    private function resolveCommitteeUserIds(array $memberIds): array
    {
        $candidateIds = collect($memberIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($candidateIds->isEmpty()) {
            return [collect(), collect()];
        }

        $usersById = User::query()
            ->whereIn('Id', $candidateIds->all())
            ->pluck('Id', 'Id');

        $remaining = $candidateIds
            ->reject(fn ($id) => $usersById->has($id))
            ->values();

        $usersByEmployee = $remaining->isNotEmpty()
            ? User::query()
                ->whereIn('EmployeeId', $remaining->all())
                ->pluck('Id', 'EmployeeId')
            : collect();

        $resolvedUserIds = $candidateIds
            ->map(function ($id) use ($usersById, $usersByEmployee) {
                // Prefer EmployeeId mapping first to support legacy forms posting Employee.Id values.
                if ($usersByEmployee->has($id)) {
                    return (int) $usersByEmployee->get($id);
                }

                if ($usersById->has($id)) {
                    return (int) $usersById->get($id);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        $unresolvedIds = $candidateIds
            ->reject(fn ($id) => $usersById->has($id) || $usersByEmployee->has($id))
            ->values();

        return [$resolvedUserIds, $unresolvedIds];
    }

    private function formatMissingEmployeeNames(Collection $missingIds): string
    {
        $ids = $missingIds
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($ids->isEmpty()) {
            return 'N/A';
        }

        $names = Employee::query()
            ->whereIn('Id', $ids->all())
            ->get(['FirstName', 'LastName'])
            ->map(fn ($employee) => trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? '')))
            ->filter()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : $ids->implode(', ');
    }

    private function addOrRestoreRfqMember(
        int $committeeId,
        int $rfqId,
        int $userId,
        int $actorId,
        Carbon $now,
        string $role
    ): void {
        $member = RFQCommitteeMember::withTrashed()
            ->where('CommitteeID', $committeeId)
            ->where('UserID', $userId)
            ->first();

        if ($member) {
            if ($member->trashed()) {
                $member->restore();
            }

            $member->update([
                'RFQID' => $rfqId,
                'Role' => $role,
                'Response' => $member->Response ?? 0,
                'IsActive' => true,
                'DeletedBy' => null,
                'DeletedOn' => null,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);
            return;
        }

        RFQCommitteeMember::create([
            'CommitteeID' => $committeeId,
            'UserID' => $userId,
            'RFQID' => $rfqId,
            'Role' => $role,
            'Response' => 0,
            'IsActive' => true,
            'HasEvaluated' => false,
            'CreatedBy' => $actorId,
            'CreatedOn' => $now,
            'ModifiedBy' => $actorId,
            'ModifiedOn' => $now,
        ]);
    }
}
