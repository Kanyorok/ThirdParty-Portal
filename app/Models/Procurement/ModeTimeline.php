<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModeTimeline extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_ModeTimelines';
    protected $primaryKey = 'Id';
    public $incrementing = true;
    protected $fillable = ['ProcurementModeId', 'Stage', 'DurationDays', 'CreatedBy', 'ModifiedBy'];

    public static function getPrimaryKey(): string
    {
        return 'ModeTimelinesId';
    }

    public function procurementMode()
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }
}
