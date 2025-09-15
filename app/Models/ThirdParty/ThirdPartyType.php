<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThirdPartyType extends Model
{
    use HasFactory;

    protected $table = 't_ThirdPartyTypes';
    protected $primaryKey = 'TypeId';
    public $timestamps = false; // using custom audit columns instead

    protected $fillable = [
        'TypeId',
        'Type', // links to CategoryMaster.Id
        'FinanceRole',
        'Code'
    ];

    protected $casts = [
        'TypeId' => 'integer',
        'Type' => 'integer',
    ];

    public function thirdParties(): BelongsToMany
    {
        return $this->belongsToMany(
            ThirdParties::class,
            't_ThirdPartyType_ThirdParties',
            'TypeId',
            'ThirdPartyId'
        );
    }
}
