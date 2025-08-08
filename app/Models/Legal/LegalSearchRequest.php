<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalSearchRequest extends Model
{
    use HasFactory;

    protected $table = 't_LegalSearchRequests';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'RequestType',
        'EntityName',
        'EntityType',
        'RegistrationNumber',
        'Country',
        'RequestedBy',
        'RequestDate',
        'Status',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'IsActive',
    ];

    // Relationships (optional example)
    public function requestedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'RequestedBy');
    }
}
