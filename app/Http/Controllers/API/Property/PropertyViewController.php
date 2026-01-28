<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\property\PropertyCollection;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyViewController extends Controller
{
    public function index(): PropertyCollection
    {
        //viewing All properties that are available for renting
        $properties = PropertyRegistry::with([
            'getBlockByProperty.floor.units' => function ($query) {
                $query->where('IsRentable', true)
                    ->where('CurrentStatus', true);
            },
        ])
        ->whereHas('getBlockByProperty.floor.units', function ($query) {
            $query->where('IsRentable', true)
                ->where('CurrentStatus', true);
        })
        ->paginate(10);

        return
            new PropertyCollection($properties);
    }


    //     {
    //             'getBlockByProperty.floor.units' => function ($query) {
    //                     ->where('CurrentStatus', true);
    //             }
    //         ])->findOrFail($id);

    //     }
}
