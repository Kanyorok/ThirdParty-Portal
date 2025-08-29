<?php

namespace App\Traits\Model;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentRelation;
use App\Services\DMS\DocumentService;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\UploadedFile;

trait DocumentsTrait
{
    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Document::class,
            DocumentRelation::class,
            'RelatedID', // Foreign key on DocumentRelation table
            'Id', // Foreign key on Document table
            'Id', // Local key on current model
            'DocumentId' // Local key on DocumentRelation table
        )->where('t_DocumentRelations.Related', self::getPrimaryKey());

    }


    /**
     * @throws ErroredException
     */
    public function newDocument(ModulesEnum $module, UploadedFile $file, array|string $permissions, User $actor): Document
    {
        // if (method_exists($this, 'getPrimaryKey')) {
        //     throw new ErroredException("Implement UserActorTrait in model");
        // }

        $RelatedId = $this->{$this->primaryKey};

        return DocumentService::createInternal($module, $file, $actor, $permissions, self::getPrimaryKey(), $RelatedId)->document;
    }

    /**
     * @throws ErroredException
     */
    public function newDocumentFromContent(ModulesEnum $module, ExtensionsEnum $extension, string $fileName, string $content, User $actor, array|string $permissions): Document
    {
        $RelatedId = $this->{$this->primaryKey};
        return DocumentService::createInternalFileContent($module, $extension, $fileName, $content, $actor, $permissions, self::getPrimaryKey(), $RelatedId)->document;
    }
}
