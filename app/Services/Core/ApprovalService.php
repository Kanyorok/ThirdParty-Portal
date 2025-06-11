<?php

namespace App\Services\Core;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct()
    {
        //
    }

    public function isDocumentApproved(string $docType, float $amount, User $actor, int $documentId): bool
    {
        return $this->isApproved($docType, $amount, $actor, $documentId);
    }

    public function isApproved(string $docType, float $amount, User $actor, $documentId ): bool
    {
        $group = DB::table('t_ApprovalGroups')->where('DocType', $docType)->first();

        if (!$group) {
            return false;
        }

        switch ($group->ApprovalType) {
            case 'AMT':
                return $this->isAmtDocumentApproved($docType, $amount, $actor);
            case 'ANY':
                return $this->isAnyApproved($docType, $actor);
            case 'MAJ':
                return $this->isMajorityApproved($docType, [$actor]); // Only 1 actor for now
            case 'ALL':

                return $this->isAllApproved($docType, $documentId);
            default:
                return false;
        }
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

        if (!$permissionId) {
            return false;
        }

        return $this->userHasPermission($actor, $permissionId);
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

        // Count users with the permission via their roles
        $count = DB::table('t_UserRoles as ur')
            ->join('t_RolePermissions as rp', 'ur.role_id', '=', 'rp.role_id')
            ->whereIn('ur.user_id', $userIds)
            ->where('rp.permission_id', $permissionId)
            ->distinct('ur.user_id')
            ->count('ur.user_id');

        $majorityThreshold = (int) ceil(count($userIds) / 2);

        return $count >= $majorityThreshold;
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
            ->value('Permission'); // permission_id
    }

    private function userHasPermission(User $user, int $permissionId): bool
    {
        return DB::table('t_UserRoles as ur')
            ->join('t_RolePermissions as rp', 'ur.role_id', '=', 'rp.role_id')
            ->where('ur.user_id', $user->Id)
            ->where('rp.permission_id', $permissionId)
            ->exists();
    }

    private function isAllApproved(string $docType, $documentId)
    {
        // 1. Get the required permission for this document type
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        if (!$permissionId) {
            return false;
        }

        // 2. Get all users who have this permission via their roles
        $allApprovers = DB::table('t_UserRoles as ur')
            ->join('t_RolePermissions as rp', 'ur.role_id', '=', 'rp.role_id')
            ->where('rp.permission_id', $permissionId)
            ->distinct('ur.user_id')
            ->pluck('ur.user_id')
            ->toArray();

        if (empty($allApprovers)) {
            return false;
        }

        // 3. Get all users who have approved this document (in t_Approvals)
        $approvedUsers = DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->whereIn('UserId', $allApprovers)
            ->distinct('UserId')
            ->pluck('UserId')
            ->toArray();

        // 4. Check if all required approvers have approved
        sort($allApprovers);
        sort($approvedUsers);

        return $allApprovers == $approvedUsers;
    }
}
