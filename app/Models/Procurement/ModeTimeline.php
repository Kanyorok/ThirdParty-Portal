<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModeTimeline extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ModeTimelines';
    protected $primaryKey = 'Id';
    public $incrementing = true;
    protected $fillable = ['ProcurementModeId', 'Stage', 'DurationDays','CreatedBy', 'ModifiedBy'];

    public function procurementMode()
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }
}