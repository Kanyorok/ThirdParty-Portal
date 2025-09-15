<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Countries';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'CountryID';
    }

    public function localities(): HasMany
    {
        return $this->hasMany(Locality::class, 'CountryId', 'Id');
    }
}
