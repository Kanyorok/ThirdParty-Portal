<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class SharedDocumentAcknowledgement extends Model
{
    protected $table = 't_HRSharedDocumentAcknowledgements';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DocumentID',
        'EmployeeID',
        'Status',
        'DueOn',
        'AcknowledgedOn',
        'AcknowledgedBy',
        'AcknowledgedIP',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'DueOn'          => 'date',
        'AcknowledgedOn' => 'datetime',
        'CreatedOn'      => 'datetime',
        'ModifiedOn'     => 'datetime',
        'DeletedOn'      => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(SharedDocument::class, 'DocumentID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
