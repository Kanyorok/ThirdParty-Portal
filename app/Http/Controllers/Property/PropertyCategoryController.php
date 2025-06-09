<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyCategoryRequest;
use App\Models\Core\CategoryMaster;
use App\Services\Property\PropertyRegistry\PropertyCategoryService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyCategory;

class PropertyCategoryController extends Controller
{
    //
    public function index()
    {
        $categories = CategoryMaster::where('Code','500000')->get();
        //dd($categories);
        return view('property.propertyregistry.propertycategory.index', compact('categories'));
    }
    
    public function create(){
        return view('property.propertyregistry.propertycategory.create');
    }
    public function store(PropertyCategoryRequest $request)
    {
        //Type and Code have been hardcoded
        $propertyCategory = PropertyCategoryService::create(
            $request->validated('Name'),
            $request->validated('Description', ''),
            $request->validated('Type','PropertyCategory'),
            $request->validated('Code','500000'),
            auth()->user()
        );

        return redirect()->route('propertycategory.index')->with('success','property category created successfully');
    }
}
