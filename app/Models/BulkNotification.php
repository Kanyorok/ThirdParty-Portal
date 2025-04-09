<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkNotification extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_BulkNotifications';
    protected $primaryKey = 'BulkNotificationID';

    protected $fillable = [
        'Label', 'Module', 'Title', 'Content', 'CompleteOn', 'Extra', 'Total',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];


    protected $casts = [
        'CompleteOn' => 'datetime',
        'Total' => 'integer',
        'Extra' => 'array'
    ];

    public function sms(): HasMany
    {
        return $this->hasMany(CrmSMS::class, 'BulkNotificationId', 'BulkNotificationID');
    }
}
