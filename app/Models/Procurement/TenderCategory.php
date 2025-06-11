<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TenderCategoryEnum;

class TenderCategory extends Model {
    use HasFactory;

    protected $primaryKey = 'Id';
    protected $table = 't_TenderCategories';
    public $incrementing = true;


    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'CategoryCode',
        'TenderCategory',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    // protected $casts = [
    //     'TenderCategory' => TenderCategoryEnum::class,
    // ];

    public static function generateCatCode(string $categoryValue = null)
    {
        $prefix = match($categoryValue) {
            TenderCategoryEnum::Goods->value => 'GDT-',
            TenderCategoryEnum::Services->value => 'SRV-',
            TenderCategoryEnum::Works->value => 'WRK-',
            default => 'CAT-'
        };

        $lastCode = self::where('CategoryCode', 'like', $prefix.'%')
                      ->orderBy('Id', 'desc')
                      ->first();

        $number = $lastCode ? (int) substr($lastCode->CategoryCode, 4) + 1 : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
