<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyRegistry;

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

        activity()->causedBy(auth()->user()->Id)->performedOn($propertyattachments)->event('create')->log("Added Property Attachment {$propertyattachments->Id}.");
        return new self($propertyattachments);
    }

}
