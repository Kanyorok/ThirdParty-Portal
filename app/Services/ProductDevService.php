<?php

namespace App\Services;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Communication\Comment;
use App\Models\Core\CodeDetail;
use App\Models\CRM\ProductDevelopment;
use App\Models\CRM\ProductDevelopmentFeature;
use App\Models\DMS\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductDevService
{
    public function __construct(public ProductDevelopment $product)
    {
    }

    public function commenting(): bool
    {
        return (!is_null($this->product->CommentStart) && is_null($this->product->CommentEnd));
    }

    /**
     * @throws ErroredException
     */
    public static function create(string $Name, string $TargetGroup, string $Notes, User $actor): self
    {
        $stages = StaticListsService::getList(StaticListsService::ProductDevelopmentStages);
        $stage = $stages->sortByDesc('DisplayOrder')->first();
        //todo fix this
        if (!$stage instanceof CodeDetail) {
            throw new ErroredException('system does not have product development stages');
        }

        $product = ProductDevelopment::create([
                                               'ProductID'   => self::_ID(),
                                               'Name'        => $Name,
                                               'TargetGroup' => $TargetGroup,
                                               'User_ID'     => $actor->Id,
                                               'Notes'       => $Notes,
                                               'StageId'     => $stage->ID,
                                               'CreatedBy'   => $actor->Id,
                                               'ModifiedBy'  => $actor->Id,
                                              ]);

        activity()->causedBy($actor)->performedOn($product)->event('create')->log('created product (' . Str::upper($product->ProductID) . ') for development.');

        return new self($product);
    }

    protected static function _ID(): string
    {
        $number = ProductDevelopment::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('ProDev-' . Str::padLeft(($number), 4, '0'));
        } while (ProductDevelopment::where('ProductID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function document(UploadedFile $file, User $actor): Image
    {
        $document = ImageService::createUpload($file, ProductDevelopment::getPrimaryKey(), $this->product->Id, $actor)->image;
        activity()->causedBy($actor)->performedOn($this->product)->event('document')->log('added a document  ' . $document->Name . ' to Product Development ' . Str::upper($this->product->ProductID) . '.');
        return $document;
    }

    public function addFeature(string $title, string $description, User $actor): ProductDevelopmentFeature
    {
        $feature = $this->product->features()->create([
                                                       'Feature'     => $title,
                                                       'Description' => $description,
                                                       'CreatedBy'   => $actor->Id,
                                                       'ModifiedBy'  => $actor->Id,
                                                      ]);
        activity()->causedBy($actor)->performedOn($this->product)->event('feature')->log('added a feature  ' . $title . ' to Product Development ' . Str::upper($this->product->ProductID) . '.');

        return $feature;
    }

    public function updateFeature(ProductDevelopmentFeature $feature, string $title, string $description, User $actor): ProductDevelopmentFeature
    {
        $feature->fill([
                        'Feature'     => $title,
                        'Description' => $description,
                        'ModifiedBy'  => $actor->Id,
                       ])->save();

        return $feature;
    }


    public function comment(string $description, User $actor, Comment $comment = null): Comment
    {
        if ($comment instanceof Comment) {
            return CommentService::forComment($comment, $description, $actor)->comment;
        }

        return CommentService::forProductDev($this->product, $description, $actor)->comment;
    }
}
