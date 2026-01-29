<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceUnderwriting extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceUnderwriting';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId', 'FeedbackDate', 'RiskScore', 'Decision', 'Comments',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
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
