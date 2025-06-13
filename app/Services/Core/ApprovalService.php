<?php

namespace App\Services\Core;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    public function isDocumentApproved(string $docType, float $amount, User $actor, int $documentId): bool
    {
        try {
            return $this->isApproved($docType, $amount, $actor, $documentId);
        } catch (\Throwable $e) {
            Log::error('Error in isDocumentApproved', ['error' => $e->getMessage()]);
            return false;
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
        $requiredPermissionId = $this->getRequiredPermissionId($docType, $amount);

        if (!$requiredPermissionId) {
            return false;
        }

        return $this->userHasPermission($actor, $requiredPermissionId);
    }

    private function isAnyApproved(string $docType, User $actor): bool
    {
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        return $permissionId && $this->userHasPermission($actor, $permissionId);
    }

    private function isMajorityApproved(string $docType, array $actors): bool
    {
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        if (!$permissionId) {
            return false;
        }

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
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        if (!$permissionId) {
            return false;
        }

        $allApprovers = DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->where('mr.model_type', 'UserID')
            ->where('rp.permission_id', $permissionId)
            ->distinct('mr.model_id')
            ->pluck('mr.model_id')
            ->toArray();

        if (empty($allApprovers)) {
            return false;
        }

        $approvedUsers = DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->whereIn('UserId', $allApprovers)
            ->distinct('UserId')
            ->pluck('UserId')
            ->toArray();

        sort($allApprovers);
        sort($approvedUsers);

        return $allApprovers == $approvedUsers;
    }

    private function getRequiredPermissionId(string $docType, float $amount): ?int
    {
        return DB::table('t_ApprovalLimits')
            ->where('DocType', $docType)
            ->where(function ($q) use ($amount) {
                $q->where('MaxAmount', '>=', $amount)
                    ->orWhereNull('MaxAmount');
            })
            ->orderBy('MaxAmount')
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
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        if (!$permissionId) {
            return [];
        }

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

        return $allApprovers->filter(function ($user) use ($approvedUserIds) {
            return !in_array($user->Id, $approvedUserIds);
        })->values()->toArray();
    }
}
