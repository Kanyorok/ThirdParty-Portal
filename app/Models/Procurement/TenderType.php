<?php

namespace App\Models\Procurement;

use App\Enums\TenderTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 't_TenderTypes';
    public $incrementing = true;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';


    protected $fillable = [
        'TypeCode',
        'TenderType',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    //     'TenderType' => TenderTypeEnum::class,
    // ];

    public static function generateTypeCode($tenderTypeValue = null)
    {
        $prefix = match ($tenderTypeValue) {
            TenderTypeEnum::Open->value => 'OPT-',
            TenderTypeEnum::Restricted->value => 'RST-',
            default => 'TYP-'
        };

        $lastType = self::withTrashed()->where('TypeCode', 'like', $prefix . '%')
                       ->orderBy('Id', 'desc')
                       ->first();

        $number = $lastType ? (int) substr($lastType->TypeCode, 4) + 1 : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
