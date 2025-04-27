<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    protected $table = 't_Tenders';
    protected $primaryKey = 'Id';
    public $incrementing = true;

    protected $fillable = [
        'TenderNumber',
        'Title',
        'Description',
        'ProcurementModeId',
        'EstimatedValue',
        'Currency',
        'StartDate',
        'Status',
        'CreatedBy',
        'ModifiedBy',
    ];

    //Relationships
    public function procurementMode()
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function stages()
    {
        return $this->hasMany(TenderStage::class, 'TenderId');
    }

    public function rfqs()
    {
        return $this->hasMany(RFQ::class, 'TenderId');
    }
}
