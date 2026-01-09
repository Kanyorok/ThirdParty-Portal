<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiPerspectiveWeight extends Model
{
    protected $table = 't_HRKPIPerspectiveWeights';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PerspectiveID',
        'PeriodID',
        'GradeID',
        'RoleID',
        'Weight',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Weight' => 'decimal:4',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function perspective()
    {
        return $this->belongsTo(KpiPerspective::class, 'PerspectiveID', 'Id');
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'PeriodID', 'Id');
    }

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID', 'Id');
    }

    public function role()
    {
        return $this->belongsTo(JobRole::class, 'RoleID', 'Id');
    }

    public static function resolveWeights(int $periodId, ?int $gradeId = null, ?int $roleId = null)
    {
        $weights = self::where('PeriodID', $periodId)
            ->where('IsActive', 1)
            ->get();

        $byPerspective = $weights->groupBy('PerspectiveID');
        $resolved = collect();

        foreach ($byPerspective as $perspectiveId => $rows) {
            $match = $rows->first(function ($row) use ($gradeId, $roleId) {
                return $row->GradeID == $gradeId && $row->RoleID == $roleId;
            });
            if (!$match && $gradeId !== null) {
                $match = $rows->first(function ($row) use ($gradeId) {
                    return $row->GradeID == $gradeId && $row->RoleID === null;
                });
            }
            if (!$match && $roleId !== null) {
                $match = $rows->first(function ($row) use ($roleId) {
                    return $row->GradeID === null && $row->RoleID == $roleId;
                });
            }
            if (!$match) {
                $match = $rows->first(function ($row) {
                    return $row->GradeID === null && $row->RoleID === null;
                });
            }
            if ($match) {
                $resolved->put($perspectiveId, $match);
            }
        }

        return $resolved;
    }
}
