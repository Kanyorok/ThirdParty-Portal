<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalObligation extends Model
{
    protected $table = 't_LegalObligations';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Title',
        'LinkedType', // 'Contract' or 'Case'
        'LinkedID',
        'DueDate',
        'Status', // Pending, Completed, Overdue
        'Description',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
        'IsActive'
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(LegalObligationAssignment::class, 'ObligationID');
    }
}
