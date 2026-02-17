<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HRM\Department;

class ExitClearanceDepartment extends Model
{
    protected $table = 't_HRExitClearanceDepartments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'DepartmentID',
        'Sequence',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function clearances(): HasMany
    {
        return $this->hasMany(ExitClearance::class, 'ClearanceDepartmentID');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    }

    public static function syncFromDepartments(?int $actorId = null): void
    {
        $departments = Department::query()
            ->whereNull('DeletedOn')
            ->orderBy('Name')
            ->get(['Id', 'Name']);

        $departmentIds = $departments->pluck('Id')->all();

        $sequence = 1;
        foreach ($departments as $department) {
            $name = trim((string) $department->Name);
            if ($name === '') {
                continue;
            }

            $existing = self::where('DepartmentID', $department->Id)->first();
            if (!$existing) {
                $existing = self::where('Name', $name)->first();
            }

            if ($existing) {
                $existing->update([
                    'Name' => $name,
                    'DepartmentID' => $department->Id,
                    'IsActive' => 1,
                    'Sequence' => $sequence,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => now(),
                ]);
            } else {
                self::create([
                    'Name' => $name,
                    'DepartmentID' => $department->Id,
                    'Sequence' => $sequence,
                    'IsActive' => 1,
                    'CreatedBy' => $actorId,
                    'CreatedOn' => now(),
                ]);
            }

            $sequence++;
        }

        if (!empty($departmentIds)) {
            self::whereNotIn('DepartmentID', $departmentIds)
                ->update([
                    'IsActive' => 0,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => now(),
                ]);
        }
    }
}
