<?php

namespace App\Traits\Model;

use App\Enums\Core\ModulesEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\DMS\Document;
use App\Services\DMS\DocumentService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

trait DocumentsTrait
{
    public function documents(): MorphMany
    {
        return $this->morphMany(__CLASS__, 'related', "Related", "RelatedID", 'Id');
    }


    /**
     * @throws ErroredException
     */
    public function newDocument(ModulesEnum $module, UploadedFile $file, array|string $permissions, User $actor): Document
    {
        if (method_exists($this, 'getPrimaryKey')) {
            throw new ErroredException("Implement UserActorTrait in model");
        }

        $Related = self::getPrimaryKey();
        $RelatedId = $this->{$this->primaryKey};

        return DocumentService::createInternal($module, $file, $actor, $permissions, $Related, $RelatedId)->document;
    }
}
