<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use App\Models\User;

class ItemCategories extends Model
{
    use UserActorTrait;

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemCategories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'CategoryCode',
        'Name',
        'Description',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    protected $casts = [
        'CategoryCode'  => 'string',
        'Name'          => 'string',
        'Description'   => 'string',
        'Status'        => 'boolean',
        'CreatedBy'     => 'integer',
        'ModifiedBy'    => 'integer',
        'DeletedBy'     => 'integer'
    ];
   
    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
}
