<?php

namespace App\Models\CRM\DebtRecovery;

use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\DebtProduct;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanAssignment extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_LoanAssignments';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'LoanAssignmentsId';
    }

    protected $fillable = [
                           'AccountID',
                           'StartOn',
                           'EndOn',
                           'Notes',
                           'UserId',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    protected $casts = [
                        'StartOn'   => 'datetime',
                        'EndOn'     => 'datetime',
                        'CreatedBy' => 'integer',
                        'UserId'    => 'integer',
                       ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id')->withTrashed();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'AccountID', 'AccountID');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(DebtProduct::class, 'AccountID', 'AccountID');
    }
}
