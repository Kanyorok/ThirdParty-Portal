<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyAttachmentsController extends Controller
{
    //
    public function index()
    {
        $propertyattachments = PropertyAttachments::all();

        return view('property.propertyregistry.propertyattachments.index',compact('propertyattachments'));
    }

    public function create(){
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.propertyattachments.create', compact('properties'));
    }
     public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyID'=>'required|string|max:20',
            'DocumentTitle'=>'required|string|max:100',
            'DocumentType'=>'required|string|max:50',
            'Description'=>'required|string|max:255',
        ]);

        $propertyattachment = PropertyAttachments::create([
            'PropertyID' => $request->PropertyID,
            'DocumentTitle' => $request->DocumentTitle,
            'DocumentType' => $request->DocumentType,
            'Description' => $request->Description,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('attachments.index')->with('success','Property attachment created successfully');
    }

}
