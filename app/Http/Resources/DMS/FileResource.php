<?php

namespace App\Http\Resources\DMS;

use App\Http\Resources\UserCollection;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class FileResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $users = (new DocumentService($this->resource))->users()->with('photo')->paginate(7, ['ImageId', 'UserID', 'Name']);
        $tags = $this->resource->tags()->paginate(4, ['TagID', 'Name', 'Visibility']);

        return [
            'id' => $this->DocumentId,
            'name' => $this->Name,
            'visibility' => [
                'value' => $this->resource->Visibility->value,
                'name' => $this->resource->Visibility->name,
                'icon' => $this->resource->Visibility->icon(),
            ],
            'type' => [
                'mime' => $this->MimeType,
                'extension' => $this->resource->ext()->value,
                'img' => $this->resource->ext()?->getIcon('img'),
                'icon' => $this->resource->ext()?->getIcon('fa')
            ],
            'size' => [
                'string' => Number::fileSize($this->resource->current->Size, 2),
                'bytes' => $this->resource->current->Size,
            ],

            'dated' => [
                'datetime' => $this->ModifiedOn->format('d M Y H:i'),
                'string' => $this->ModifiedOn->diffForHumans(),
            ],
            'users' => [
                'data' => new UserCollection($users),
                'total' => $users->total() - 7,
                'hasMorePages' => $users->hasMorePages(),
            ],
            'tags' => [
                'data' => new DMSTagsCollection($tags),
                'total' => $tags->total() - 4,
                'hasMorePages' => $tags->hasMorePages(),
            ],
            'links' => [
                'summary' => route('files.edit', [$this->resource->repository->RepositoryId, $this->DocumentId])
            ]
        ];
    }
}
