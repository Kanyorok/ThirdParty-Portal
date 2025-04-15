<?php

namespace App\Models;

use App\Enums\LocalityTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locality extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_Localities';
    protected $primaryKey = 'ID';

    protected $fillable = [
                           'Name',
                           'LocationType',
                           'LocalityID',
                           'IsActive',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'LocationType' => LocalityTypeEnum::class,
                       ];

    public function in(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'LocalityID', 'ID');
    }
}
