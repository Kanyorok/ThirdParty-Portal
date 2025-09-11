<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyAttachmentsRequest;
use App\Services\Property\PropertyRegistry\PropertyAttachmentsService;
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
        //$this->authorize(PermissionEnum::PropertyAttachmentsCreate, PropertyAttachments::class);
        $properties = PropertyRegistry::all();
        $documenttypes = CodeDetail::where('CodeID', 'DocumentType')->get();
        return view('property.propertyregistry.propertyattachments.create', compact('properties', 'documenttypes'));
    }

    public function store(PropertyAttachmentsRequest $request)
    {
     //$this->authorize(PermissionEnum::PropertyAttachmentsCreate, PropertyAttachments::class);
        //dd($request->all());
        $validated = $request->validated();
        
        $PropertyID = PropertyRegistry::findOrFail($validated['PropertyID']);
        $DocumentType = CodeDetail::findOrFail($validated['DocumentType']);
        $user = Auth::user();

        $propertyattachment = PropertyAttachmentsService::create(
            $PropertyID,
            $validated['DocumentTitle'],
             $DocumentType,
            $validated['Description'] ?? '',
            Auth::user()
        );
        return redirect()->route('attachments.index')->with('success', 'Property attachment created successfully');
    }

    public function edit($Id)
    {
   // $this->authorize(PermissionEnum::PropertyAttachmentsView, PropertyAttachments::class);
    $propertyattachments = PropertyAttachments::findOrFail($Id);
    $properties = PropertyRegistry::all();
    $documenttypes = CodeDetail::where('CodeID', 'DocumentType')->get();


    return view('property.propertyregistry.propertyattachments.edit', compact('propertyattachments', 'properties','documenttypes'));
    }

    public function update(PropertyAttachmentsRequest $request, $Id)
    {
    // $this->authorize(PermissionEnum::PropertyAttachmentsUpdate, PropertyAttachments::class);
        $validated = $request->validated();

    DB::beginTransaction();

    try {
        $propertyattachments = PropertyAttachments::findOrFail($Id);

        $propertyattachments->update([
            'PropertyID'         => $validated['PropertyID'],
            'DocumentTitle'         => $validated['DocumentTitle'],
            'DocumentType'         => $validated['DocumentType'],
            'Description'         => $validated['Description'] ?? '',
            'ModifiedBy'       => Auth::id(),
        ]);

        DB::commit();

        activity()
            ->performedOn($propertyattachments)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'update'])
            ->log('Updated Property Attachments');

        return redirect()->route('attachments.index')
                         ->with('success', 'Property attachments updated successfully');
    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error('Failed to update property attachments: ' . $th->getMessage());

        return back()->withErrors(['error' => 'Failed to update property attachments'])->withInput();
    }
}

public function destroy($Id)
{
  //    $this->authorize(PermissionEnum::PropertyAttachmentsDelete, PropertyAttachmentsRequest::class);
    try {
        $propertyattachments = PropertyAttachments::findOrFail($Id);
        $propertyattachments->delete();

        return redirect()->route('attachments.index')
                         ->with('success', 'Property attachments deleted successfully!');
    } catch (\Throwable $th) {
        Log::error('Error deleting property attachments: ' . $th->getMessage());

        return redirect()->back()
                         ->withErrors(['error' => 'Failed to delete property attachments. Please try again.'])
                         ->withInput();
    }
  }
}

