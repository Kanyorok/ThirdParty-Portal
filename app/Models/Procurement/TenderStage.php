<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class TenderStage extends Model
{
    protected $table = 't_TenderStages';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TenderId', 'Stage', 'DurationDays', 'StartDate', 'EndDate',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderId');
    }
}
