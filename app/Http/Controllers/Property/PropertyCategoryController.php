<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyCategory;

class PropertyCategoryController extends Controller
{
    //
    public function index()
    {
        $categories = PropertyCategory::all();
        //dd($categories);
        return view('property.propertyregistry.propertycategory.index', compact('categories'));
    }
    
    public function create(){
        return view('property.propertyregistry.propertycategory.create');
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyCategoryName'=>'required|string|max:255',
            'Description'=>'nullable|string|max:255',
        ]);

        PropertyCategory::create($request->only('PropertyCategoryName', 'Description')+[
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('propertycategory.index')->with('success','property category created successfully');
    }
}
