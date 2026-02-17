<?php

namespace App\Models\CRM;

use App\Enums\TonalityEnum;
use App\Models\BR\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Reviews';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'BranchID',
                           'Party',
                           'PartyID',
                           'Source',
                           'SourceID',
                           'Tonality',
                           'Rating',
                           'Content',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'Rating' => 'integer',
                        'Tonality' => TonalityEnum::class,
                       ];

    public static function getPrimaryKey(): string
    {
        return 'ReviewID';
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'OurBranchID');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Source', 'SourceID');
    }
}
