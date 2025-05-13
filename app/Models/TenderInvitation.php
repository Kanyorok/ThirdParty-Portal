<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderInvitation extends Model
{
    // Table name (optional if follows Laravel convention)
    protected $table = 't_TenderInvitations';

    // Mass assignable attributes
    protected $fillable = [
        'TenderID',
        'SupplierID',
        'InvitationDate',
        'ResponseStatus',
        'ResponseDate',
        'DeclineReason',
        'ConfirmationAttachment',
    ];

    // Casts for automatic type conversion
    protected $casts = [
        'InvitationDate' => 'datetime',
        'ResponseDate' => 'datetime',
    ];

    // Enum-like accessor for response status
    public const STATUS_PENDING = 'Pending';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_DECLINED = 'Declined';

    // Relationships (assuming relevant models exist)
    //public function tender()
   // {
   //     return $this->belongsTo(Tender::class, 'TenderID');
   // }

   // public function supplier()
   // {
     //   return $this->belongsTo(Supplier::class, 'SupplierID');
   // }
}
