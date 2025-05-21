<?php

namespace App\Models\HRM;

use App\Models\ThirdParies\Board;
use App\Models\ThirdParies\BoardCommittee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Committees';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           "CommitteeID",
                           "Name",
                           'Notes',
                           'Type',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'CommitteeID';
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 't_BoardCommittee', 'CommitteeId', 'BoardId', 'Id', 'Id')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy'])->using(BoardCommittee::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 't_Committee_Employee', 'CommitteeId', 'EmployeeId')
            ->withPivot(['CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn']);
    }
}