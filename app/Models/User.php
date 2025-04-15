<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GenderEnum;
use App\Models\BR\Branch;
use App\Services\UserService;
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
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use ImageTrait;
    use HasFactory;
    use Notifiable;
    use UserActorTrait;
    use SoftDeletes;
    use HasRoles;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Users';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'UserID',
                           'Name',
                           'Email',
                           'Phone',
                           'ImageId',
                           'Gender',
                           'Linked',
                           'Notes',
                           'Password',
                           'Email_Signature',
                           'BranchId',
                           'ClientID',
                           'ExtensionNo',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $hidden = [
                         'Password',
                         'remember_token',
                         'Linked',
                         'Email_Signature',
                        ];

    protected $casts = [
        //  'Password' => 'hashed',
                        'Gender'    => GenderEnum::class,
                        'Linked'    => 'bool',
                        'CreatedBy' => 'integer',
                       ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
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
        return $this->morphMany(CrmEmail::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(CrmSMS::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function loansAssigned(): HasMany
    {
        return $this->hasMany(\App\Models\DebtRecovery\LoanAssignment::class, 'UserId', 'Id');
    }

    public function role(): ?Role
    {
        $role = $this->roles()->first();
        return ($role instanceof Role) ? $role : null;
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(CRMImage::class, 'ImageId', 'ImageID');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'OurBranchID');
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

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function sendPasswordResetNotification($token = ''): void
    {
        (new UserService($this))->sendPasswordResetNotification();
    }

    public function scopeHasPermission(Builder $query, string $permission): Builder
    {
        return $query->whereHas('roles.permissions', function (Builder $query) use ($permission) {
            $query->where('name', $permission);
        })->orWhereHas('permissions', function (Builder $query) use ($permission) {
            $query->where('name', $permission);
        });
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'party', "Party", "PartyID", 'Id');
    }
}
