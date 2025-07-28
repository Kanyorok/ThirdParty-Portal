<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyAttachmentsRequest;
use App\Services\Property\PropertyRegistry\PropertyAttachmentsService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\Core\CodeDetail;

class PropertyAttachmentsController extends Controller
{
    //
    public function index()
    {
        $propertyattachments = PropertyAttachments::all();

        return view('property.propertyregistry.propertyattachments.index', compact('propertyattachments'));
    }

    public function create(){
        $properties = PropertyRegistry::all();
        $documentTypes = CodeDetail::where('CodeID', 'DocumentType')->get();
        return view('property.propertyregistry.propertyattachments.create', compact('properties', 'documentTypes'));
    }

    public function store(PropertyAttachmentsRequest $request)
    {
        //dd($request->all());
        $validated = $request->validated();
        
        $PropertyID = PropertyRegistry::findOrFail($validated['PropertyID']);
        $documentType = CodeDetail::findOrFail($validated['DocumentType']);
        $user = Auth::user();

        $propertyattachment = PropertyAttachmentsService::create(
            $PropertyID,
            $validated['DocumentTitle'],
            $documentType,
            $validated['Description'],
            auth()->user()
        );
        return redirect()->route('attachments.index')->with('success', 'Property attachment created successfully');
    }

}

