<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationResult extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationResults';
    protected $primaryKey = 'ResultID';

    protected $fillable = [
        'ApplicationID',
        'TotalScore',
        'Decision',
        'ApprovalBy',
    ];

    protected $casts = [
        'TotalScore' => 'float',
        'ApprovalDate' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PrequalificationApplication::class, 'ApplicationID', 'ApplicationID');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ApprovalBy', 'UserID');
    }
}
