<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalClause extends Model
{
    protected $table = 't_LegalClauses';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Title', 'ClauseType', 'Content', 'IsStandard', 'Version',
        'IsActive', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn'
    ];
}
