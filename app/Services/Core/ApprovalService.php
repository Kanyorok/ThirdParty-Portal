<?php

namespace App\Services\Core;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{

    public function isDocumentApproved(string $docType, float $amount, User $actor, int $documentId): array
    {
        try {
            if ($this->isFullyApproved($docType, $documentId, $amount)) {
                return [
                    'approved' => false,
                    'message' => 'This document has already been fully approved.',
                ];
            }

            $approved = $this->isApproved($docType, $amount, $actor, $documentId);

            return [
                'approved' => $approved,
                'message' => $approved
                    ? 'Approval granted.'
                    : 'You are not authorized to approve this document.',
            ];
        } catch (\Throwable $e) {
            Log::error('Error in isDocumentApproved', ['error' => $e->getMessage()]);

            return [
                'approved' => false,
                'message' => 'An error occurred while checking approval status.',
            ];
        }
    }

    private function isApproved(string $docType, float $amount, User $actor, int $documentId): bool
    {
        $group = DB::table('t_ApprovalGroups')->where('DocType', $docType)->first();

        if (!$group) {
            return false;
        }

        return match ($group->ApprovalType) {
            'AMT' => $this->isAmtDocumentApproved($docType, $amount, $actor),
            'ANY' => $this->isAnyApproved($docType, $actor),
            'MAJ' => $this->isMajorityApproved($docType, [$actor]),
            'ALL' => $this->isAllApproved($docType, $documentId),
            default => false,
        };
    }

    private function isAmtDocumentApproved(string $docType, float $amount, User $actor): bool
    {
        $permissionId = $this->getRequiredPermissionId($docType, $amount);
        return $permissionId && $this->userHasPermission($actor, $permissionId);
    }

    private function isAnyApproved(string $docType, User $actor): bool
    {
        $permissionId = $this->getApprovalGroupPermission($docType);
        return $permissionId && $this->userHasPermission($actor, $permissionId);
    }

    private function isMajorityApproved(string $docType, array $actors): bool
    {
        $permissionId = $this->getApprovalGroupPermission($docType);

        $userIds = array_map(fn($user) => $user->Id, $actors);
        $count = DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->where('mr.model_type', 'UserID')
            ->whereIn('mr.model_id', $userIds)
            ->where('rp.permission_id', $permissionId)
            ->distinct('mr.model_id')
            ->count('mr.model_id');

        return $count >= ceil(count($userIds) / 2);
    }

    private function isAllApproved(string $docType, int $documentId): bool
    {
        $permissionId = $this->getApprovalGroupPermission($docType);
        if (!$permissionId) return false;

        $allApprovers = $this->getApproverIds($permissionId);
        $approvedUsers = $this->getApprovedUserIds($docType, $documentId, $allApprovers);

        sort($allApprovers);
        sort($approvedUsers);

        return $allApprovers === $approvedUsers;
    }

    private function getRequiredPermissionId(string $docType, float $amount): ?int
    {
        return DB::table('t_ApprovalLimits')
            ->where('DocType', $docType)
            ->where(function ($q) use ($amount) {
                $q->where('MaxAmount', '>=', $amount)->orWhereNull('MaxAmount');
            })
            ->orderBy('MaxAmount')
            ->value('Permission');
    }

    private function getApprovalGroupPermission(string $docType): ?int
    {
        return DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');
    }

    private function userHasPermission(User $user, int $permissionId): bool
    {
        return DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->where('mr.model_type', 'UserID')
            ->where('mr.model_id', $user->Id)
            ->where('rp.permission_id', $permissionId)
            ->exists();
    }

    public function getPendingApprovers(string $docType, int $documentId): array
    {
        $permissionId = $this->getApprovalGroupPermission($docType);
        if (!$permissionId) return [];

        $allApprovers = DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->join('t_Users as u', 'mr.model_id', '=', 'u.Id')
            ->where('mr.model_type', 'UserID')
            ->where('rp.permission_id', $permissionId)
            ->select('u.Id', 'u.name', 'u.email')
            ->distinct()
            ->get();

        $approvedUserIds = DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->pluck('UserId')
            ->toArray();

        return $allApprovers->filter(fn($user) => !in_array($user->Id, $approvedUserIds))->values()->toArray();
    }

    public function isFullyApproved(string $docType, int $documentId, float $amount): bool
    {
        $group = DB::table('t_ApprovalGroups')->where('DocType', $docType)->first();
        if (!$group) return false;

        return match ($group->ApprovalType) {
            'ALL' => $this->isAllApproved($docType, $documentId),
            'ANY' => $this->isAnyAlreadyApproved($docType, $documentId),
            'MAJ' => $this->isMajorityAlreadyApproved($docType, $documentId),
            'AMT' => $this->isAmtAlreadyApproved($docType, $documentId, $amount),
            default => false,
        };
    }

    private function isAnyAlreadyApproved(string $docType, int $documentId): bool
    {
        $permissionId = $this->getApprovalGroupPermission($docType);
        return DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->whereIn('UserId', $this->getApproverIds($permissionId))
            ->exists();
    }

    private function isMajorityAlreadyApproved(string $docType, int $documentId): bool
    {
        $permissionId = $this->getApprovalGroupPermission($docType);
        $approverIds = $this->getApproverIds($permissionId);
        $approvedUserIds = $this->getApprovedUserIds($docType, $documentId, $approverIds);

        return count($approvedUserIds) >= ceil(count($approverIds) / 2);
    }

    private function isAmtAlreadyApproved(string $docType, int $documentId, float $amount): bool
    {
        $permissionId = $this->getRequiredPermissionId($docType, $amount);
        return DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->whereIn('UserId', $this->getApproverIds($permissionId))
            ->exists();
    }

    private function getApproverIds(int $permissionId): array
    {
        return DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->where('mr.model_type', 'UserID')
            ->where('rp.permission_id', $permissionId)
            ->pluck('mr.model_id')
            ->toArray();
    }

    private function getApprovedUserIds(string $docType, int $documentId, array $approverIds): array
    {
        return DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->whereIn('UserId', $approverIds)
            ->pluck('UserId')
            ->unique()
            ->toArray();
    }
}
