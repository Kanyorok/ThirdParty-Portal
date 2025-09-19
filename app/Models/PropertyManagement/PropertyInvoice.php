<?php

namespace App\Models\PropertyManagement;
use App\Enums\Property\PropertyInvoiceEnum;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class PropertyInvoice extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_RentInvoice';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InvoiceNumber',
        'Lease',
        'BillingMonth',
        'InvoiceDate',
        'RentAmount',
        'ServicesCharge',
        'OtherCharges',
        'InvoiceNotes',
        'ParkingFee',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyInvoiceId';
    }
    protected $casts = [
        'Status' => PropertyInvoiceEnum::class,
    ];

    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'Lease', 'Id');
    }
    public function receipts()
    {
        return $this->hasMany(PropertyReceipt::class, 'InvoiceID', 'Id');
    }
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

}
