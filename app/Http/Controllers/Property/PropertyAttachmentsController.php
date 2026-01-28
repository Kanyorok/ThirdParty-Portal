<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyAttachmentsRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Services\Property\PropertyRegistry\PropertyAttachmentsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyAttachmentsController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyAttachmentsView, PropertyAttachments::class);
        $propertyattachments = PropertyAttachments::all();

        return view('property.propertyregistry.propertyattachments.index', compact('propertyattachments'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyAttachmentsCreate, PropertyAttachments::class);
        $properties = PropertyRegistry::all();
        $documenttypes = CodeDetail::where('CodeID', 'DocumentType')->get();

        return view('property.propertyregistry.propertyattachments.create', compact('properties', 'documenttypes'));
    }

    public function store(PropertyAttachmentsRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyAttachmentsCreate, PropertyAttachments::class);
        $validated = $request->validated();

        $PropertyID = PropertyRegistry::findOrFail($validated['PropertyID']);
        $DocumentType = CodeDetail::findOrFail($validated['DocumentType']);
        $user = Auth::user();

        foreach ($request->file('file', []) as $uploadedFile) {
            $propertyattachment = PropertyAttachmentsService::create(
                $PropertyID,
                $validated['DocumentTitle'],
                $DocumentType,
                $validated['Description'] ?? '',
                $request->user(),
                $uploadedFile
            );
        }

        return redirect()->route('attachments.index')->with('success', 'Property attachment created successfully');
    }

    public function edit($Id)
    {

        $this->authorize(PermissionEnum::PropertyAttachmentsView, PropertyAttachments::class);
        $propertyattachments = PropertyAttachments::findOrFail($Id);
        $properties = PropertyRegistry::all();
        $documenttypes = CodeDetail::where('CodeID', 'DocumentType')->get();


        return view('property.propertyregistry.propertyattachments.edit', compact('propertyattachments', 'properties', 'documenttypes'));
    }

    public function update(PropertyAttachmentsRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyAttachmentsUpdate, PropertyAttachments::class);

        $validated = $request->validated();

        // Fetch model instances
        $PropertyID = PropertyRegistry::findOrFail($validated['PropertyID']);
        $DocumentType = CodeDetail::findOrFail($validated['DocumentType']);
        $user = Auth::user();


        DB::beginTransaction();

        try {
            // Get existing attachment
            $attachment = PropertyAttachments::findOrFail($id);

            // Update base details first
            PropertyAttachmentsService::update(
                $attachment,
                $PropertyID,
                $validated['DocumentTitle'],
                $DocumentType,
                $validated['Description'] ?? '',
                $user
            );

            // Handle new files (if uploaded)
            foreach ($request->file('file', []) as $uploadedFile) {
                PropertyAttachmentsService::update(
                    $attachment,
                    $PropertyID,
                    $validated['DocumentTitle'],
                    $DocumentType,
                    $validated['Description'] ?? '',
                    $user,
                    $uploadedFile
                );
            }

            DB::commit();

            return redirect()->route('attachments.index')
                ->with('success', 'Property attachment updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update property attachment: ' . $th->getMessage());

            return back()
                ->withErrors(['error' => 'Failed to update property attachment'])
                ->withInput();
        }
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::PropertyAttachmentsDelete, PropertyAttachmentsRequest::class);

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
