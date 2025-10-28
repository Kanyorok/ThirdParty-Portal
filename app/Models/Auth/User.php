<?php

namespace App\Models\Auth;

use App\Enums\Employee\GenderEnum;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\Core\Task;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Models\CRM\Ticket;
use App\Models\HRM\Employee;
use App\Models\Settings\ApprovalStage;
use App\Services\HRM\UserService;
use App\Traits\Controller\HasBranchRoles;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable
{
    use ImageTrait, HasFactory, Notifiable, UserActorTrait, SoftDeletes, HasBranchRoles;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Users';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'UserID', 'Name', 'Email', 'Phone', 'ImageId', 'Linked', 'EmployeeId', 'Notes', 'Password', 'Email_Signature', 'ClientID', 'ExtensionNo',
       'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $hidden = [
        'Password', 'remember_token', 'Linked', 'Email_Signature',
    ];

    protected $casts = [
        'Gender'    => GenderEnum::class,
        'Linked'    => 'bool',
        'CreatedBy' => 'integer',
    ];

    protected ?Role $effectiveRole = null;

    // ✅ FIXED: Use Id instead of UserID for polymorphic relationships
    public function getRoleNames(): Collection
    {
        $branchId = session('LoginBranchId');
        if (!$branchId) return collect();

        return ModelRole::where('model_id', $this->Id)  // Changed from $this->UserID
            ->where('model_type', self::getPrimaryKey())
            ->where('BranchId', $branchId)
            ->with('role')
            ->get()
            ->pluck('role.name')
            ->filter();
    }

    public function hasRole($roles, string $guard = null): bool
    {
        $roleNames = $this->getRoleNames();
        return collect($roles)->intersect($roleNames)->isNotEmpty();
    }

    // ✅ FIXED: Use Id instead of UserID
    public function getPermissionsViaRoles(): Collection
    {
        $branchId = session('LoginBranchId');
        if (!$branchId) return collect();

        return Permission::query()
            ->whereHas('roles.modelRoles', function ($query) use ($branchId) {
                $query->where('model_id', $this->Id)  // Changed from implicit binding
                    ->where('model_type', self::getPrimaryKey())
                    ->where('BranchId', $branchId);
            })
            ->get();
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->getPermissionsViaRoles()->contains('name', $permission);
    }

    public function setEffectiveRole(string $roleName): void
    {
        $this->effectiveRole = Role::where('name', $roleName)->first();
    }

    // ✅ FIXED: Explicitly use Id for model_id
    public function syncRolesWithBranch(array|Collection $roles, int $branchId, int $actorId = 1): void
    {
        // Remove existing roles for this user + branch
        ModelRole::where([
            'model_id' => $this->Id,  // Explicitly use Id
            'model_type' => self::getPrimaryKey(),
            'BranchId' => $branchId,
        ])->delete();

        foreach ($roles as $role) {
            $roleModel = $role instanceof Role
                ? $role
                : Role::where('name', $role)->firstOrFail();

            ModelRole::create([
                'model_id' => $this->Id,  // Explicitly use Id
                'model_type' => self::getPrimaryKey(),
                'role_id' => $roleModel->id,
                'BranchId' => $branchId,
                'CreatedBy' => $actorId,
                'CreatedOn' => now(),
                'ModifiedBy' => $actorId,
                'ModifiedOn' => now(),
            ]);
        }
    }

    // Keep this as is - used for routing and morph map
    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeId', 'Id');
    }

    // Keep this as is - used for routing
    public function getRouteKeyName(): string
    {
        return 'UserID';
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'UserID', "Id");
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(Email::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function loansAssigned(): HasMany
    {
        return $this->hasMany(LoanAssignment::class, 'UserId', 'Id');
    }

    public function ApprovalStages(): HasMany
    {
        return $this->hasMany(ApprovalStage::class, 'CreatedBy', 'Id');
    }

    //  FIXED: Use Id for polymorphic lookup
    public function role(): ?Role
    {
        // Return memory-injected role if available
        if ($this->effectiveRole instanceof Role) {
            return $this->effectiveRole;
        }

        $branchId = session('LoginBranchId');
        if (!$branchId) {
            return null;
        }

        // Find branch-specific role via t_ModelRoles
        $modelRole = ModelRole::where('model_id', $this->Id)  // Changed from implicit
            ->where('model_type', self::getPrimaryKey())
            ->where('BranchId', $branchId)
            ->first();

        if (!$modelRole) {
            return null;
        }

        return Role::find($modelRole->role_id);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 't_TeamUser', 'UserId', 'TeamId', 'Id', 'TeamID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy']);
    }

    public function teamUser(): HasMany
    {
        return $this->hasMany(TeamUser::class, 'UserId', "Id");
    }

    public function branchRoles()
    {
        return $this->hasMany(ModelRole::class, 'model_id', 'Id')  // Added explicit foreign/local key
            ->where('model_type', self::getPrimaryKey())
            ->with(['role', 'branch']);
    }

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function sendPasswordResetNotification($token = ''): void
    {
        (new UserService($this))->sendPasswordResetNotification();
    }

    public function scopeHasPermission(Builder $query, string|array $permissions): Builder
    {
        if (is_string($permissions)) {
            $permissions = explode(',', $permissions);
        }
        return $query->whereHas('roles.permissions', function (Builder $query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->orWhereHas('permissions', function (Builder $query) use ($permissions) {
            $query->whereIn('name', $permissions);
        });
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'party', "Party", "PartyID", 'Id');
    }

    protected function getImageName(): string
    {
        return $this->Name;
    }
}