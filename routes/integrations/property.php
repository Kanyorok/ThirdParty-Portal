<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Property\PropertyViewController;

// Route::prefix('property')->middleware(PropertyAuthMiddleware::class)->group(function () {
//     Route::get('rentable-properties', [PropertyViewController::class, 'rentableProperties']);
//     // Route::get('property-structure/{id}', [PropertyViewController::class, 'propertyStructure']);
// });

Route::get('properties/rentable-properties', [PropertyViewController::class, 'index']);

