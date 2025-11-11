<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Employee\GenderEnum;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'UserID', 'Name', 'Email', 'Phone', 'ImageId', 'Linked', 'EmployeeId', 'Notes', 'Password', 'Email_Signature', 'ClientID', 'ExtensionNo',
        'BranchId', 'login_at', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $hidden = [
        'Password', 'remember_token', 'Linked', 'Email_Signature', 'BranchId'
    ];

    protected $casts = [
        'Gender'    => GenderEnum::class,
        'Linked'    => 'bool',
        'login_at' => 'datetime',
        'CreatedBy' => 'integer',
    ];

    protected ?Role $effectiveRole = null;

    public function getRoleNames(): Collection
    {
        $branchId = session('LoginBranchId');
        if (!$branchId) return collect();

    return ModelRole::where('model_id', $this->Id)
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

    public function getPermissionsViaRoles(): Collection
    {
        $branchId = session('LoginBranchId');
        if (!$branchId) return collect();

        return Permission::query()
            ->whereHas('roles.modelRoles', function ($query) use ($branchId) {
                $query->where('model_id', $this->Id)
                    ->where('model_type', self::getPrimaryKey())
                    ->where('BranchId', $branchId);
            })
            ->get();
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->getPermissionsViaRoles()->contains('name', $permission);
    }


    public function syncRolesWithBranch(array|Collection $roles, int $branchId, int $actorId = 1): void
    {
        // Remove existing roles for this user + branch
        ModelRole::where([
            'model_id' => $this->Id,
            'model_type' => self::getPrimaryKey(),
            'BranchId' => $branchId,
        ])->delete();

        foreach ($roles as $role) {
            $roleModel = $role instanceof Role
                ? $role
                : Role::where('name', $role)->firstOrFail();

            ModelRole::create([
                'model_id' => $this->Id,
                'model_type' => self::getPrimaryKey(),
                'role_id' => $roleModel->id,
                'BranchId' => $branchId,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function employee():BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeId', 'Id');
    }

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

    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        )->withPivot(['BranchId'/*, 'CreatedBy', 'ModifiedBy'*/])->withTimestamps()->where('t_ModelRoles.BranchId', $this->BranchId);
    }

    public function role()
    {
        return $this->roles()?->latest('id')->first();
        /*
          $branchRole =  ModelRole::where('model_id', $this->getKey())
              ->where('model_type', self::getPrimaryKey())
              ->where('BranchId', $this->BranchId)
              ->with('role')
              ->first();

          return  $branchRole->role;*/
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
        return $this->hasMany(ModelRole::class, 'model_id')
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
