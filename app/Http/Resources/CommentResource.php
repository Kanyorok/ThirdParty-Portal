<?php

namespace App\Http\Resources;

use App\Models\Communication\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $s = $this->resource;
        if (! $s instanceof Comment) {
            $s = $s->resource;
        }
        $service = new CommentService($s);

        return [
                'id' => $this->Id,
                'msg' => $this->Notes,
                'dated' => [
                                 'datetime' => $this->CreatedOn?->format('M d, Y h:i a'),
                                 'sting' => $this->CreatedOn?->diffForHumans(),
                                ],
                'actor' => $service->commenter(),
                'extra' => $service->extras(),
                'permission' => [
                                 'cancelable' => $service->trashable(auth()->user()),
                                ],
               ];
    }
}
