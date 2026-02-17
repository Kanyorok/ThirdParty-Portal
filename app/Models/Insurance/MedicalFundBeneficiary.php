<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalFundBeneficiary extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_MedicalFundBeneficiaries';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundId',
        'ContributorId',
        'FullName',
        'Relationship',
        'DateOfBirth',
        'NationalID',
        'Contact',
        'IsActive',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn','DeletedBy','DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundBeneficiaryId';
    }

    public function fund()
    {
        return $this->belongsTo(MedicalFund::class, 'FundId', 'Id');
    }

    public function contributor()
    {
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorId', 'Id');
    }

    /**
     * Human readable relationship description.
     * The `Relationship` field stores either the Code (string) or an ID depending on older code paths.
     */
    public function getRelationshipDisplayAttribute()
    {
        if (blank($this->Relationship)) {
            return null;
        }

        // If the stored value is numeric treat it as the CodeDetail primary key
        if (is_numeric($this->Relationship)) {
            $byId = CodeDetail::find((int) $this->Relationship);
            if ($byId) {
                return $byId->Description;
            }
        }

        // Otherwise, try to match the CodeID (common pattern in this app)
        $byCodeId = CodeDetail::where('CodeID', 'BeneficiaryRelationship')->first();
        if ($byCodeId) {
            return $byCodeId->Description;
        }

        // As a last resort try matching Description directly
        $byDescription = CodeDetail::where('Description', $this->Relationship)->first();
        if ($byDescription) {
            return $byDescription->Description;
        }

        // Nothing resolved: return the raw stored value
        return $this->Relationship;
    }
}
