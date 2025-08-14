<?php

namespace App\Models\Procurement\Prequalification;

use App\Traits\Model\UserActorTrait;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PrequalificationRound extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationRounds';
    protected $primaryKey = 'RoundID';

    protected $fillable = [
        'Title',
        'Description',
        'StartDate',
        'EndDate',
        'MaxVendors',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'EndDate' => 'date',
        'Status' => PrequalificationRoundEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return $this->primaryKey;
    }

    public static function getPrimaryKey(): string
    {
        return (new static())->primaryKey;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'UserID');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PrequalificationApplication::class, 'RoundID', 'RoundID');
    }

    public function prequalificationSections()
    {
        return $this->hasMany(PrequalificationSection::class, 'RoundId', 'RoundID')
            ->with('masterSection', 'criteria');
    }

    public function prequalificationCriteria()
    {
        return $this->hasMany(PrequalificationCriteria::class, 'RoundId', 'RoundID')
            ->with(['masterCriteria', 'masterSection']);
    }


    public function masterSections(): HasManyThrough
    {
        return $this->hasManyThrough(
            Section::class,
            PrequalificationSection::class,
            'RoundID',
            Section::getPrimaryKey(),
            'RoundID',
            'SectionId'
        );
    }
}
