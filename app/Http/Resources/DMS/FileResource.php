<?php

namespace App\Http\Resources\DMS;

use App\Http\Resources\UserCollection;
use App\Services\DMS\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class FileResource extends JsonResource
{
    protected bool $minified = false;

    public function setMinified(bool $minified = false): static
    {
        $this->minified = $minified;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $extra = [];
        if (! $this->minified) {
            $service = new DocumentService($this->resource);
            $users = $service->users()->with('photo')->paginate(7, ['ImageId', 'UserID', 'Name']);
            $tags = $service->tags(auth()->user())->paginate(4, ['t_DMSTags.TagID', 't_DMSTags.Name', 't_DMSTags.Visibility']);
            $extra = [
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
            ];
        }

        return array_merge([
            'id' => $this->DocumentId,
            'name' => Str::limit(implode(".", array_slice(explode('.', $this->Name), 0, -1)), 45, '* *') . '.' . $this->resource->ext()->value,
            'visibility' => [
                'value' => $this->resource->Visibility->value,
                'name' => $this->resource->Visibility->name,
                'icon' => $this->resource->Visibility->icon(),
            ],
            'type' => [
                'mime' => $this->MimeType,
                'extension' => $this->resource->ext()->value,
                'img' => $this->resource->ext()?->getIcon('img'),
                'icon' => $this->resource->ext()?->getIcon('fa'),
            ],
            'size' => [
                'string' => Number::fileSize($this->resource->current->Size, 2),
                'bytes' => $this->resource->current->Size,
            ],
            'dated' => [
                'datetime' => $this->ModifiedOn->format('d M Y H:i'),
                'string' => $this->ModifiedOn->diffForHumans(),
            ],
            'links' => [
                'detail' => route('files.show', [$this->resource->repository->RepositoryId, $this->DocumentId]),
                'summary' => route('files.edit', [$this->resource->repository->RepositoryId, $this->DocumentId]),
                'move' => route('file-move.index', ['document' => $this->DocumentId]),
            ],
        ], $extra);
    }
}
