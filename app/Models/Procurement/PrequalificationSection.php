<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationSection extends Model
{
    use SoftDeletes, UserActorTrait;
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_PrequalificationRoundSections';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'RoundId',
        'SectionId',
        'Weight',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'prequalificationsectionId';
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'SectionId');
    }

    public function round()
    {
        return $this->belongsTo(PrequalificationPeriod::class, 'RoundId');
    }

}
