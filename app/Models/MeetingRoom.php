<?php

namespace App\Models;

use App\Models\BR\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoom extends Model
{
    //,''
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_MeetingRooms';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'RoomID', 'Name', 'Capacity', 'Extra', 'BranchId',
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Extra' => 'object',
        'Capacity' => 'integer',
    ];

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'LocationId', 'Id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'OurBranchID');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'RoomID';
    }
}
