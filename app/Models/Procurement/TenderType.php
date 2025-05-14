<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TenderTypeEnum;

class TenderType extends Model 
{
    use HasFactory;

    protected $primaryKey = 'Id';
    protected $table = 't_TenderTypes';
    public $incrementing = true;

    protected $fillable = [
        'TypeCode',
        'TenderType',
        'Description',
    ];

    protected $casts = [
        'TenderType' => TenderTypeEnum::class,
    ];

    public static function generateTypeCode(string $tenderTypeValue = null)
    {
        $prefix = match($tenderTypeValue) {
            TenderTypeEnum::Open->value => 'OPT-',  
            TenderTypeEnum::Restricted->value => 'RST-',
            default => 'TYP-' 
        };

        $lastType = self::where('TypeCode', 'like', $prefix.'%')
                       ->orderBy('Id', 'desc')
                       ->first();

        $number = $lastType ? (int) substr($lastType->TypeCode, 4) + 1 : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}