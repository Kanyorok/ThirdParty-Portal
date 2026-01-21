<?php

namespace App\Models\Auth;

use App\Enums\Employee\GenderEnum;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\Core\Branch;
use App\Models\Core\Task;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Models\CRM\Ticket;
use App\Models\HRM\Employee;
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

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Users';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'UserID',
        'Name',
        'Email',
        'Phone',
        'ImageId',
        'Linked',
        'EmployeeId',
        'Notes',
        'Password',
        'Email_Signature',
        'ClientID',
        'ExtensionNo',
        'BranchId',
        'login_at',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
        'Linked',
        'Email_Signature',
        'BranchId'
    ];

    protected $casts = [
        'Gender'    => GenderEnum::class,
        'Linked'    => 'bool',
        'login_at' => 'datetime',
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
        // No special bypass - admin role gets permissions like any other role
        return $this->getPermissionsViaRoles()->contains('name', $permission);
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

            // Prevent duplicate assignment using updateOrCreate logic or check-then-create
            // We use firstOrCreate to avoid duplicates if run multiple times
            ModelRole::firstOrCreate([
                'model_id' => $this->Id,  // Explicitly use Id
                'model_type' => self::getPrimaryKey(),
                'BranchId' => $branchId,
            ], [
                'role_id' => $roleModel->id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            // If the role was different, we might want to update it, but requirements say "syncRolesWithBranch" usually implies setting THE role for that branch.
            // If we strictly want to overwrite the role for that branch:
            ModelRole::where([
                'model_id' => $this->Id,
                'model_type' => self::getPrimaryKey(),
                'BranchId' => $branchId,
            ])->update([
                'role_id' => $roleModel->id,
                'ModifiedOn' => now(),
                'DeletedOn' => null, // Restore if soft deleted
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

    /**
     * Get the morph class for the model.
     * This must return 'UserID' to match the morphMap and database model_type column.
     * 
     * Without this override, Spatie Permission looks for model_type = 'App\Models\Auth\User'
     * but the database has model_type = 'UserID' as defined in the morphMap.
     */
    public function getMorphClass()
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

        return  $modelRole?->role;
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

    public function branchRoles(): HasMany
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

    /**
     * Scopes a query to include models that have specific permissions, per current login branch.
     *
     * @param Builder $query The query builder instance.
     * @param string|array $permissions The permission(s) to filter the query by. Can be a string of comma-separated values or an array of permission names.
     * @return Builder The modified query builder instance.
     */
    public function scopeHasPermission(Builder $query, string|array $permissions): Builder
    {
        if (is_string($permissions)) {
            $permissions = array_map('trim', explode(',', $permissions));
        }
        return $query->whereHas('roles.permissions', function (Builder $query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->orWhereHas('permissions', function (Builder $query) use ($permissions) {
            $query->whereIn('name', $permissions);
        });
    }

    public function scopeHasBranchPermissionRole(Builder $query, string|array $permissions, string|array $branches): Builder
    {
        if (is_string($permissions)) {
            $permissions = array_map('trim', explode(',', $permissions));
        }
        if (is_string($branches)) {
            $branches = array_map('trim', explode(',', $branches));
        }


        return $query->whereHas('branchRoles', function (Builder $query) use ($permissions, $branches) {
            $query->where(function (Builder $q) use ($branches) {
                $q->whereIn('t_ModelRoles.BranchId', $branches);
            })->whereHas('role.permissions', function (Builder $q) use ($permissions) {
                $q->whereIn('t_Permissions.name', $permissions);
            });
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }
}
