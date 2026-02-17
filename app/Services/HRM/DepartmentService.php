<?php

namespace App\Services\HRM;

use App\Models\Auth\User;
use App\Models\HRM\Department;
use Illuminate\Support\Str;

class DepartmentService
{
    public function __construct(public Department $department)
    {
    }

    public static function create(string $name, User $actor, string $description = null): self
    {
        $department = Department::create([
            'Name' => $name,
            'DepartmentID' => self::_id(),
            'Description' => $description,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
        activity()->causedBy($actor)->performedOn($department)->event('create')->log('Created Department ' . $department->DepartmentID);

        return new self($department);
    }

    public function setHOD(User $hod, User $actor): static
    {
        $this->department->update([
            'HeadId' => $hod->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->department)->event('update')->log('set a hod (' . $hod->Id . ') to department ' . $this->department->DepartmentID);

        return $this;
    }

    public function setDeputyHOD(User $user, User $actor): static
    {
        $this->department->update([
            'DeputyHeadId' => $user->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->department)->event('update')->log('set a deputy hod (' . $user->Id . ') to department ' . $this->department->DepartmentID);

        return $this;
    }

    protected static function _id(): string
    {
        $number = Department::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('D' . Str::padLeft(($number), 4, '0'));
        } while (Department::query()->where('DepartmentID', $slug)->withTrashed()->exists());

        return $slug;
    }
}
