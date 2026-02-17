<?php

namespace App\Traits\Controller;

use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Models\HRM\Department;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait EmployeeTrait
{
    public function getEmployees(Builder|BelongsToMany $query, array $with = [], array $extra = []): JsonResponse
    {
        if (! empty($with)) {
            $query->with($with);
        }

        try {
            return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
                ->addColumn('action', function (Employee $employee) {
                    return '<a  href="' . route('employees.show', [$employee->EmployeeNo]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('branch.Name', function (Employee $employee) use ($with) {
                    if (in_array('branch', $with, true)) {
                        if ($employee->branch instanceof Branch) {
                            return $employee->branch->Name;
                        }

                        return ' ? ';
                    }

                    return '';
                })->editColumn('department.Name', function (Employee $employee) use ($with) {
                    if (in_array('department', $with, true)) {
                        if ($employee->department instanceof Department) {
                            return $employee->department->Name;
                        }

                        return ' ? ';
                    }

                    return '';
                })->editColumn('EmployeeNo', function (Employee $employee) {
                    return strtoupper($employee->EmployeeNo);
                })->editColumn('photo', function (Employee $employee) use ($with) {
                    return (in_array('photo', $with, true)) ?
                        $employee->getImage('class="img-thumbnail" style="height: 70px; width: 70px;"')
                        : '';
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (Employee $employee) {
                        return route('employees.show', [$employee->EmployeeNo]);
                    },
                ])->rawColumns(['action', 'photo'])->make();
        } catch (Exception $e) {
        }

        return $this->errored('fetching data failed, try again later');
    }
}
