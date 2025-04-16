<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ModeTimeline extends Model
{
    protected $table = 't_ModeTimelines';
    protected $primaryKey = 'Id';
    public $incrementing = true;
    protected $fillable = ['ProcurementModeId', 'Stage', 'DurationDays'];

    public function procurementMode()
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }
}