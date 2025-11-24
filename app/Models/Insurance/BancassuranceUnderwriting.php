<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceUnderwriting extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceUnderwriting';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId', 'FeedbackDate', 'RiskScore', 'Decision', 'Comments',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimarykey(): string
    {
        return 'bancassuranceunderwitingId';
    }

    public function decision()
    {
        return $this->belongsTo(CodeDetail::class, 'Decision', 'ID');
    }
}
