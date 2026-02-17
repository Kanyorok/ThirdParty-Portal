<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiScoreChart extends Model
{
    protected $table = 't_HRKPIScoreCharts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'RatingScaleID',
        'MinPercent',
        'MaxPercent',
        'RatingValue',
        'RatingLabel',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'MinPercent' => 'decimal:2',
        'MaxPercent' => 'decimal:2',
        'RatingValue' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function ratingScale()
    {
        return $this->belongsTo(KpiRatingScale::class, 'RatingScaleID', 'Id');
    }
}
