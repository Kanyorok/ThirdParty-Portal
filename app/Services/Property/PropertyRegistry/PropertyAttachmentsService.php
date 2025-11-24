<?php

namespace App\Services\Property\PropertyRegistry;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyRegistry;
use Illuminate\Http\UploadedFile;

class PropertyAttachmentsService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyAttachments $propertyAttachments)
    {
    }

    public static function create(
        PropertyRegistry  $PropertyID,
        string         $DocumentTitle,
        CodeDetail      $DocumentType,
        string        $Description = null,
         User          $user,
        UploadedFile $document = null
    ): self
    {
        $propertyattachments = PropertyAttachments::create([
            'PropertyID' => $PropertyID->Id,
            'DocumentTitle' => $DocumentTitle,
            'DocumentType' => $DocumentType->ID,
            'Description' => $Description,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        if ($document) {
            $propertyattachments->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyRegistryView->value],
                $user
            );
        }

        activity()->causedBy(auth()->user()->Id)->performedOn($propertyattachments)->event('create')->log("Added Property Attachment {$propertyattachments->Id}.");
        return new self($propertyattachments);
    }

    public static function update(
        PropertyAttachments $attachment,
        PropertyRegistry    $PropertyID,
        string              $DocumentTitle,
        CodeDetail          $DocumentType,
        ?string             $Description,
        User                $user,
        UploadedFile        $document = null
    ): self
    {
        $attachment->update([
            'PropertyID' => $PropertyID->Id,
            'DocumentTitle' => $DocumentTitle,
            'DocumentType' => $DocumentType->Id,
            'Description' => $Description,
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
        ]);

        if ($document) {
            $attachment->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyAttachmentsView->value],
                $user
            );
        }

        activity()
            ->causedBy($user->Id)
            ->performedOn($attachment)
            ->event('update')
            ->log("Updated Property Attachment {$attachment->Id}.");

        return new self($attachment);
    }


}
