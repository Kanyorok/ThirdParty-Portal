<?php

namespace App\Models\Procurement\Prequalification;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationResult extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
