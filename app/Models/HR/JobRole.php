<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

/**
 * JobRole model - now backed by t_Roles (unified roles table).
 *
 * Filters to only 'job' type roles by default via a global scope.
 * This keeps backward compatibility: all existing code that uses
 * JobRole::where('IsActive', 1)->get() will continue to work
 * but now reads from t_Roles where role_type = 'job'.
 */
class JobRole extends Model
{
    protected $table = 't_Roles';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'guard_name',
        'Code',
        'Name',
        'GradeID',
        'DepartmentID',
        'Description',
        'IsActive',
        'role_type',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'DeletedOn',
    ];

    protected static function booted(): void
    {
        // Only return job-type roles from this model
        static::addGlobalScope('job_roles', function ($query) {
            $query->where('role_type', 'job');
        });

        // Auto-set role_type when creating via this model
        static::creating(function ($model) {
            $model->role_type = 'job';
            if (empty($model->guard_name)) {
                $model->guard_name = config('auth.defaults.guard', 'web');
            }
            // Map HR field names to Spatie field names
            if (! empty($model->Name) && empty($model->name)) {
                $model->name = $model->Name;
            }
        });
    }

    /**
     * Accessor: Map Spatie 'name' to HR 'Name' for backward compatibility.
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    /**
     * Accessor: Map 'id' to 'Id' for backward compatibility.
     */
    public function getIdAttribute()
    {
        return $this->attributes['id'] ?? null;
    }

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\HRM\Department::class, 'DepartmentID');
    }
}
