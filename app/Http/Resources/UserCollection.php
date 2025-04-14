<?php

namespace App\Http\Resources;

use App\Models\BR\Client;
use App\Services\OperatorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
                'data' => $this->collection->transform(function ($user) {
                    $operatorService = new OperatorService($user->OperatorID);
                    return [
                            'id'    => $user->OperatorID,
                            'name'  => ($operatorService->getClient() instanceof Client) ? $operatorService->getClient()->Name : '',
                            'image' => $operatorService->getImage(),
                           ];
                }),
               ];
    }
}
