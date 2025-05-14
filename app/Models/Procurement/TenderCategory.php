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

    protected $fillable = [
        'CategoryCode',
        'TenderCategory',
        'Description',
    ];  

    protected $casts = [
        'TenderCategory' => TenderCategoryEnum::class,
    ];

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