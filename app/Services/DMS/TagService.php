<?php

namespace App\Services\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Models\Auth\User;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use Illuminate\Support\Str;

class TagService
{
    public function __construct(public DMSTags $tag)
    {
    }

    public static function create(string $Name, string $Description, User $actor, VisibilityEnum $visibility): self
    {
        $tag = new DMSTags();
        $tag->fill([
            'Name' => $Name,
            'TagID' => self::_id(),
            'Visibility' => $visibility->value,
            'Description' => $Description,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($tag->refresh())->event('create')->log('created ' . $tag->Name . ' document tag.');

        return new self($tag);
    }

    private static function _id(): string
    {
        $number = DMSTags::query()->withTrashed()->count();

        do {
            $number++;
            $slug = "T" . Str::of($number)->padLeft(4);
        } while (DMSTags::where('TagID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function attach(Document $document, User $actor): static
    {
        $document->tags()->attach($this->tag, [
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($document)->event('attach')->log('attached ' . $this->tag->Name . ' to ' . $document->Name . '.');

        return $this;
    }
}
