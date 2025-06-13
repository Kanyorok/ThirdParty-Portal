<?php

namespace App\Services\Core;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    public function __construct()
    {
        //
    }

    public function isDocumentApproved(string $docType, float $amount, User $actor, int $documentId): bool
    {
        try {
            return $this->isApproved($docType, $amount, $actor, $documentId);
        } catch (\Throwable $e) {
            Log::error('Error in isDocumentApproved', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function isApproved(string $docType, float $amount, User $actor, $documentId): bool
    {
        try {
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
                    return $this->isMajorityApproved($docType, [$actor]);
                case 'ALL':
                    return $this->isAllApproved($docType, $documentId);
                default:
                    return false;
            }
        } catch (\Throwable $e) {
            Log::error('Error in isApproved', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isAmtDocumentApproved(string $docType, float $amount, User $actor): bool
    {
        try {
            $requiredPermissionId = $this->getRequiredPermissionId($docType, $amount);
            if (!$requiredPermissionId) {
                return false;
            }
            return $this->userHasPermission($actor, $requiredPermissionId);
        } catch (\Throwable $e) {
            Log::error('Error in isAmtDocumentApproved', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isAnyApproved(string $docType, User $actor): bool
    {
        try {
            $permissionId = DB::table('t_ApprovalGroups')
                ->where('DocType', $docType)
                ->value('Permission');

            if (!$permissionId) {
                return false;
            }

            return $this->userHasPermission($actor, $permissionId);
        } catch (\Throwable $e) {
            Log::error('Error in isAnyApproved', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isMajorityApproved(string $docType, array $actors): bool
    {
        try {
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

            $majorityThreshold = (int) ceil(count($userIds) / 2);

            return $count >= $majorityThreshold;
        } catch (\Throwable $e) {
            Log::error('Error in isMajorityApproved', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function getRequiredPermissionId(string $docType, float $amount): ?int
    {
        try {
            return DB::table('t_ApprovalLimits')
                ->where('DocType', $docType)
                ->where(function ($q) use ($amount) {
                    $q->where('MaxAmount', '>=', $amount)
                        ->orWhereNull('MaxAmount');
                })
                ->orderBy('MaxAmount')
                ->value('Permission');
        } catch (\Throwable $e) {
            Log::error('Error in getRequiredPermissionId', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function userHasPermission(User $user, int $permissionId): bool
    {
        try {
            return DB::table('t_ModelRoles as mr')
                ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
                ->where('mr.model_type', 'UserID')
                ->where('mr.model_id', $user->Id)
                ->where('rp.permission_id', $permissionId)
                ->exists();
        } catch (\Throwable $e) {
            Log::error('Error in userHasPermission', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isAllApproved(string $docType, $documentId): bool
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error('Error in isAllApproved', ['error' => $e->getMessage()]);
            return false;
        }
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
