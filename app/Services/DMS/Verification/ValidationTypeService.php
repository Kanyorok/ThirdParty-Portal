<?php

namespace App\Services\DMS\Verification;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\DMS\DocumentValidationType;
use App\Services\Core\PermissionsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ValidationTypeService extends PermissionsService
{
    public function __construct(public DocumentValidationType $type)
    {
    }

    /**
     * @throws ErroredException
     */
    public static function create(string $Name, User $actor, string $Notes = null, Collection $approvers = null): self
    {
        $type = new DocumentValidationType();
        $type->fill([
            "ValidationTypeId" => self::_id(),
            "Name" => $Name,
            "Notes" => $Notes,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($type)->event('create')->log('Created Document Validation Type  ' . $type->ValidationTypeId);

        $service = (new self($type))->addApprover($actor, SystemHelper::user(), false);
        if ($approvers) {
            foreach ($approvers as $approver) {
                $service->addApprover($approver, $actor);
            }
        }
        return $service;
    }

    protected static function _id(): string
    {
        $number = DocumentValidationType::query()->withTrashed()->count();
        do {
            $number++;
            $slug = "ValType" . Str::of($number)->padLeft(3, '0');
        } while (DocumentValidationType::where('ValidationTypeId', $slug)->withTrashed()->exists());

        return $slug;
    }

    /**
     * @throws ErroredException
     */
    public function addApprover(User|Team $watcher, User $actor, bool $notify = true): static
    {
        $this->_addPermissions($this->type, $watcher, RoleEnum::Admin, $actor, $notify);
        return $this;
    }
}
