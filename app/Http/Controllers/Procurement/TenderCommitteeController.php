<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCommittee;
use App\Models\Procurement\TenderCommitteeMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderCommitteeController extends Controller
{
    public function index(Request $request)
    {
        $tenderCommittees = TenderCommittee::query()
            ->with('tender')
            ->withCount(['members as active_members_count' => function ($query) {
                $query->where('IsActive', true);
            }])
            ->get()
            ->map(function ($item) {
                $committeeKey = (int) ($item->getKey() ?? 0);

                return [
                    'id' => $committeeKey ?: ($item->TenderID ?? 0),
                    'committee_id' => $committeeKey,
                    'type' => 'tender',
                    'ref' => $item->tender->TenderNo ?? ('TNDR-' . ($item->ReferenceId ?? $item->TenderID ?? 'N/A')),
                    'refId' => $item->ReferenceId ?? $item->TenderID,
                    'description' => $item->tender->Title ?? 'N/A',
                    'members_count' => (int) ($item->active_members_count ?? 0),
                    'appointment_date' => $item->AppointmentDate,
                    'is_active' => (bool) $item->IsActive,
                ];
            });

        $rfqCommittees = RFQCommittee::query()
            ->with('rfq')
            ->withCount(['members as active_members_count' => function ($query) {
                $query->where('IsActive', true);
            }])
            ->get()
            ->map(function ($item) {
                $committeeKey = (int) ($item->getKey() ?? 0);

                return [
                    'id' => $committeeKey ?: ($item->RFQID ?? 0),
                    'committee_id' => $committeeKey,
                    'type' => 'rfq',
                    'ref' => $item->rfq->RFQNumber ?? 'N/A',
                    'refId' => $item->RFQID,
                    'description' => $item->rfq->Comments ?? 'N/A',
                    'members_count' => (int) ($item->active_members_count ?? 0),
                    'appointment_date' => $item->AppointmentDate,
                    'is_active' => (bool) $item->IsActive,
                ];
            });

        $committeesCollection = collect($tenderCommittees)
            ->merge($rfqCommittees)
            ->sortByDesc('appointment_date')
            ->values();

        $perPage = 10;
        $page = (int) $request->query('page', 1);
        $total = $committeesCollection->count();
        $currentPageItems = $committeesCollection->forPage($page, $perPage)->values();
        $committees = new LengthAwarePaginator($currentPageItems, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // Only users linked to employees can be assigned to committees.
        $employees = User::query()
            ->with(['employee.role:Id,Name'])
            ->whereNotNull('EmployeeId')
            ->orderBy('Name')
            ->get(['Id', 'Name', 'EmployeeId']);

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.index', compact(
            'committees',
            'employees'
        ));
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'committeeType' => 'required|in:tender',
            'referenceId' => 'required|integer|exists:t_Tenders,Id',
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
        $hasActiveCommittee = TenderCommittee::query()
            ->where('CommitteeType', 'tender')
            ->where(function ($query) use ($referenceId) {
                $query->where('ReferenceId', $referenceId)
                    ->orWhere('TenderID', $referenceId);
            })
            ->where('IsActive', true)
            ->exists();

        if ($hasActiveCommittee) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An active committee already exists for this tender.');
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = Carbon::now();
            $committeeName = 'Tender Committee for #' . $referenceId;

            $committee = TenderCommittee::create([
                'CommitteeType' => 'tender',
                'ReferenceId' => $referenceId,
                'TenderID' => $referenceId,
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
                $this->addOrRestoreTenderMember(
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
                    'committeeType' => 'tender',
                    'referenceId' => $referenceId,
                    'member_count' => $resolvedUserIds->count(),
                ])
                ->log('Committee and members appointed successfully.');

            return redirect()->back()->with('success', 'Tender committee successfully appointed.');
        } catch (\Throwable $th) {
            DB::rollBack();

            $activity = activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => 'tender',
                    'referenceId' => $request->referenceId,
                ]);

            if (isset($committee)) {
                $activity->performedOn($committee);
            }

            $activity->log('Failed to appoint committee: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to appoint committee: ' . $th->getMessage());
        }
    }

    public function show($id, $type)
    {
        $committeeMembers = collect();
        $availableMembers = collect();
        $title = null;
        $committee = null;

        if ($type === 'tender') {
            $tender = Tender::find($id);
            if (! $tender) {
                return redirect()->back()->with('error', 'Tender not found with ID: ' . $id);
            }

            $title = $tender->Title;
            $committee = TenderCommittee::query()
                ->where('CommitteeType', 'tender')
                ->where(function ($query) use ($id) {
                    $query->where('ReferenceId', $id)
                        ->orWhere('TenderID', $id);
                })
                ->orderByDesc('IsActive')
                ->orderByDesc('Id')
                ->first();

            if ($committee) {
                $committeeKey = (int) ($committee->getKey() ?? 0);
                $committeeMembers = TenderCommitteeMember::query()
                    ->with(['committee', 'user.employee', 'userByEmployee.employee'])
                    ->where(function ($query) use ($committeeKey, $id) {
                        if ($committeeKey > 0) {
                            $query->where('CommitteeID', $committeeKey)
                                ->orWhere('TenderID', $id);
                        } else {
                            $query->where('TenderID', $id);
                        }
                    })
                    ->whereNull('DeletedOn')
                    ->orderBy('id')
                    ->get();
            }
        } elseif ($type === 'rfq') {
            $rfq = RFQ::find($id);
            if (! $rfq) {
                return redirect()->back()->with('error', 'RFQ not found with ID: ' . $id);
            }

            $title = $rfq->RFQNumber;
            $committee = RFQCommittee::query()
                ->where('RFQID', $id)
                ->orderByDesc('IsActive')
                ->orderByDesc('Id')
                ->first();

            if ($committee) {
                $committeeKey = (int) ($committee->getKey() ?? 0);
                $committeeMembers = RFQCommitteeMember::query()
                    ->with(['committee', 'user.employee', 'userByEmployee.employee'])
                    ->where(function ($query) use ($committeeKey, $id) {
                        if ($committeeKey > 0) {
                            $query->where('CommitteeID', $committeeKey)
                                ->orWhere('RFQID', $id);
                        } else {
                            $query->where('RFQID', $id);
                        }
                    })
                    ->whereNull('DeletedOn')
                    ->orderBy('id')
                    ->get();
            }
        } else {
            return redirect()->back()->with('error', 'Unsupported committee type.');
        }

        if (! $committee) {
            return redirect()->back()->with('error', 'No committee found for this reference.');
        }
        $committeeKey = (int) ($committee->getKey() ?? 0);

        $currentMemberValues = $committeeMembers
            ->pluck('UserID')
            ->filter(fn ($value) => ! is_null($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $availableMembers = User::query()
            ->with(['employee.role:Id,Name'])
            ->whereNotNull('EmployeeId')
            ->when($currentMemberValues->isNotEmpty(), function ($query) use ($currentMemberValues) {
                $query->whereNotIn('Id', $currentMemberValues->all())
                    ->whereNotIn('EmployeeId', $currentMemberValues->all());
            })
            ->orderBy('Name')
            ->get(['Id', 'Name', 'EmployeeId']);

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.TenderMembers', [
            'committeeMembers' => $committeeMembers,
            'availableMembers' => $availableMembers,
            'tenderTitle' => $title,
            'tenderID' => $id,
            'committeeID' => $committeeKey,
            'committeeType' => $type,
            'isCommitteeActive' => (bool) $committee->IsActive,
            'appointmentDate' => $committee->AppointmentDate,
        ]);
    }

    public function membersAdd(Request $request)
    {
        $request->validate([
            'committeeType' => 'required|in:tender,rfq',
            'tenderID' => 'required|integer',
            'committeeID' => 'nullable|integer',
            'memberID' => 'nullable|array',
            'memberID.*' => 'integer',
            'memberRole' => 'nullable|array',
            'memberRole.*' => 'nullable|string|max:100',
            'newMembers' => 'nullable|array',
            'newMembers.*' => 'integer',
            'removeMembers' => 'nullable|array',
            'removeMembers.*' => 'integer',
        ]);

        $now = Carbon::now();
        $userId = (int) Auth::id();
        $committeeType = $request->committeeType;
        $referenceId = (int) $request->tenderID;
        $committeeId = $request->filled('committeeID') ? (int) $request->committeeID : null;

        $committee = $this->resolveCommitteeForManagement($committeeType, $referenceId, $committeeId);
        if (! $committee) {
            return redirect()->back()->with('error', 'Committee not found for the selected reference.');
        }

        if (! (bool) $committee->IsActive) {
            return redirect()->back()->with('error', 'This committee is inactive and cannot be modified.');
        }

        [$newUserIds, $unresolvedNewIds] = $this->resolveCommitteeUserIds((array) $request->input('newMembers', []));
        if ($unresolvedNewIds->isNotEmpty()) {
            $missing = $this->formatMissingEmployeeNames($unresolvedNewIds);

            return redirect()->back()->with('error', 'Some selected new members do not have active user accounts: ' . $missing);
        }

        DB::beginTransaction();

        try {
            $committeeKey = (int) ($committee->getKey() ?? 0);
            $updatedCount = 0;
            $addedCount = 0;
            $removedCount = 0;

            $existingIds = (array) $request->input('memberID', []);
            $existingRoles = (array) $request->input('memberRole', []);
            foreach ($existingIds as $index => $rawMemberId) {
                $rawMemberId = (int) $rawMemberId;
                if ($rawMemberId <= 0) {
                    continue;
                }

                $role = $existingRoles[$index] ?? 'Member';
                $resolvedUserId = $this->resolveCommitteeUserId($rawMemberId);
                $candidateIds = collect([$rawMemberId, $resolvedUserId])
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($candidateIds)) {
                    continue;
                }

                if ($committeeType === 'tender') {
                    $member = TenderCommitteeMember::withTrashed()
                        ->where('CommitteeID', $committeeKey)
                        ->whereIn('UserID', $candidateIds)
                        ->orderByDesc('Id')
                        ->first();
                } else {
                    $member = RFQCommitteeMember::withTrashed()
                        ->where('CommitteeID', $committeeKey)
                        ->whereIn('UserID', $candidateIds)
                        ->orderByDesc('Id')
                        ->first();
                }

                if (! $member) {
                    continue;
                }

                if (method_exists($member, 'trashed') && $member->trashed()) {
                    $member->restore();
                }

                $member->update([
                    'UserID' => $resolvedUserId ?? $member->UserID,
                    'Role' => $role,
                    'IsActive' => true,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                ]);
                $updatedCount++;
            }

            foreach ($newUserIds as $newUserId) {
                if ($committeeType === 'tender') {
                    $this->addOrRestoreTenderMember(
                        $committeeKey,
                        $referenceId,
                        (int) $newUserId,
                        $userId,
                        $now,
                        'Member'
                    );
                } else {
                    $this->addOrRestoreRfqMember(
                        $committeeKey,
                        $referenceId,
                        (int) $newUserId,
                        $userId,
                        $now,
                        'Member'
                    );
                }
                $addedCount++;
            }

            $removeIds = collect((array) $request->input('removeMembers', []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            foreach ($removeIds as $rawRemoveId) {
                $resolvedUserId = $this->resolveCommitteeUserId((int) $rawRemoveId);
                $candidateIds = collect([(int) $rawRemoveId, $resolvedUserId])
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($candidateIds)) {
                    continue;
                }

                if ($committeeType === 'tender') {
                    $members = TenderCommitteeMember::query()
                        ->where('CommitteeID', $committeeKey)
                        ->whereIn('UserID', $candidateIds)
                        ->whereNull('DeletedOn')
                        ->get();
                } else {
                    $members = RFQCommitteeMember::query()
                        ->where('CommitteeID', $committeeKey)
                        ->whereIn('UserID', $candidateIds)
                        ->whereNull('DeletedOn')
                        ->get();
                }

                foreach ($members as $member) {
                    $member->update([
                        'IsActive' => false,
                        'DeletedBy' => $userId,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                    ]);
                    $member->delete();
                    $removedCount++;
                }
            }

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => $committeeType,
                    'referenceId' => $referenceId,
                    'committeeId' => $committeeKey,
                    'updatedCount' => $updatedCount,
                    'addedCount' => $addedCount,
                    'removedCount' => $removedCount,
                ])
                ->log('Committee members updated successfully.');

            return redirect()->back()->with('success', 'Committee members updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => $committeeType,
                    'referenceId' => $referenceId,
                ])
                ->log('Failed to update committee members: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to update committee members: ' . $th->getMessage());
        }
    }

    public function getReferences($type)
    {
        if ($type === 'tender') {
            $activeReferenceIds = TenderCommittee::query()
                ->where('CommitteeType', 'tender')
                ->where('IsActive', true)
                ->get(['ReferenceId', 'TenderID'])
                ->flatMap(function ($committee) {
                    return [
                        $committee->ReferenceId ? (int) $committee->ReferenceId : null,
                        $committee->TenderID ? (int) $committee->TenderID : null,
                    ];
                })
                ->filter()
                ->unique()
                ->values();

            $data = Tender::query()
                ->when($activeReferenceIds->isNotEmpty(), function ($query) use ($activeReferenceIds) {
                    $query->whereNotIn('Id', $activeReferenceIds->all());
                })
                ->select('Id', 'TenderNo as RefNo', 'Title')
                ->get();
        } elseif ($type === 'rfq') {
            $activeRfqIds = RFQCommittee::query()
                ->where('IsActive', true)
                ->pluck('RFQID')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $data = RFQ::query()
                ->when($activeRfqIds->isNotEmpty(), function ($query) use ($activeRfqIds) {
                    $query->whereNotIn('Id', $activeRfqIds->all());
                })
                ->select('Id', 'RFQNumber as RefNo')
                ->get();
        } else {
            return response()->json([], 400);
        }

        return response()->json($data);
    }

    private function resolveCommitteeForManagement(string $committeeType, int $referenceId, ?int $committeeId)
    {
        if ($committeeType === 'tender') {
            return TenderCommittee::query()
                ->when($committeeId, function ($query) use ($committeeId) {
                    $query->where('Id', $committeeId);
                }, function ($query) use ($referenceId) {
                    $query->where('CommitteeType', 'tender')
                        ->where(function ($subQuery) use ($referenceId) {
                            $subQuery->where('ReferenceId', $referenceId)
                                ->orWhere('TenderID', $referenceId);
                        })
                        ->orderByDesc('IsActive')
                        ->orderByDesc('Id');
                })
                ->first();
        }

        return RFQCommittee::query()
            ->when($committeeId, function ($query) use ($committeeId) {
                $query->where('Id', $committeeId);
            }, function ($query) use ($referenceId) {
                $query->where('RFQID', $referenceId)
                    ->orderByDesc('IsActive')
                    ->orderByDesc('Id');
            })
            ->first();
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

    private function resolveCommitteeUserId(int $memberId): ?int
    {
        [$resolved, ] = $this->resolveCommitteeUserIds([$memberId]);

        return $resolved->first();
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

    private function addOrRestoreTenderMember(
        int $committeeId,
        int $tenderId,
        int $userId,
        int $actorId,
        Carbon $now,
        string $role
    ): void {
        $member = TenderCommitteeMember::withTrashed()
            ->where('CommitteeID', $committeeId)
            ->where('UserID', $userId)
            ->first();

        if ($member) {
            if ($member->trashed()) {
                $member->restore();
            }

            $member->update([
                'TenderID' => $tenderId,
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

        TenderCommitteeMember::create([
            'CommitteeID' => $committeeId,
            'UserID' => $userId,
            'TenderID' => $tenderId,
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