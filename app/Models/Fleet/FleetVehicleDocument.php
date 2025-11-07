<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetVehicleDocument extends Model
{
    protected $table = 't_FleetVehicleDocuments';
    protected $primaryKey = 'DocumentID';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'DocumentType',
        'DocumentNumber',
        'IssueDate',
        'ExpiryDate',
        'FilePath',
        'Notes',
        'CreatedBy',
        'CreatedOn',
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
