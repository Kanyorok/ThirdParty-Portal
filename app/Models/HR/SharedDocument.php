<?php

namespace App\Models\HR;

use App\Models\DMS\Document;
use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Model;

class SharedDocument extends Model
{
    protected $table = 't_HRSharedDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CategoryID',
        'ParentID',
        'Title',
        'Description',
        'Version',
        'EffectiveDate',
        'ExpiryDate',
        'AcknowledgementDueOn',
        'OwnerDepartmentID',
        'AccessLevel',
        'IsDownloadable',
        'IsMandatory',
        'Language',
        'Status',
        'DocumentId',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'EffectiveDate'        => 'date',
        'ExpiryDate'           => 'date',
        'AcknowledgementDueOn' => 'date',
        'IsDownloadable'       => 'boolean',
        'IsMandatory'          => 'boolean',
        'IsActive'             => 'boolean',
        'CreatedOn'            => 'datetime',
        'ModifiedOn'           => 'datetime',
        'DeletedOn'            => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(SharedDocumentCategory::class, 'CategoryID');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'ParentID');
    }

    public function ownerDepartment()
    {
        return $this->belongsTo(Department::class, 'OwnerDepartmentID');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 't_HRSharedDocumentDepartments', 'DocumentID', 'DepartmentID');
    }

    public function roles()
    {
        return $this->belongsToMany(JobRole::class, 't_HRSharedDocumentRoles', 'DocumentID', 'RoleID');
    }

    public function acknowledgements()
    {
        return $this->hasMany(SharedDocumentAcknowledgement::class, 'DocumentID');
    }
}
