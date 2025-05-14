<?php

namespace App\Models\Communication;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkNotification extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_BulkNotifications';
    protected $primaryKey = 'BulkNotificationID';

    protected $fillable = [
                           'Label',
                           'Module',
                           'Title',
                           'Content',
                           'CompleteOn',
                           'Extra',
                           'Total',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];


    protected $casts = [
                        'CompleteOn' => 'datetime',
                        'Total'      => 'integer',
                        'Extra'      => 'array',
                       ];

    public function sms(): HasMany
    {
        return $this->hasMany(SMS::class, 'BulkNotificationId', 'BulkNotificationID');
    }
}
