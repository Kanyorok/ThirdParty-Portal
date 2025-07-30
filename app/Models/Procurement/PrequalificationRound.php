<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Procurement\PrequalificationCriteria;
use App\Models\Procurement\PrequalificationApplication;
use App\Models\Procurement\PrequalificationSection;

class PrequalificationRound extends Model
{
    use SoftDeletes;

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
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'ModifieBy' => 'integer',
        'DeletedBy' => 'integer',
        'EndDate' => 'datetime',
        'StartDate' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Id', 'UserID');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PrequalificationSection::class, 'RoundID', 'RoundID');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(PrequalificationCriteria::class, 'RoundID', 'RoundID');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PrequalificationApplication::class, 'RoundID', 'RoundID');
    }
}
