<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Committees';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           "CommitteeID",
                           "Name",
                           'Notes',
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
}
