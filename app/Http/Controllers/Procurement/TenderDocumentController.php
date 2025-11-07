<?php
namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TenderDocumentController extends Controller
{
    public function index($tenderId)
    {
        $documents = TenderDocument::where('TenderID', $tenderId)
            ->with(['creator', 'modifier'])
            ->get();
            
        return response()->json($documents);
    }

    public function store(Request $request, $tenderId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255'
        ]);

        $file = $request->file('file');
        $path = $file->store('tender-documents');

        $document = TenderDocument::create([
            'TenderDocumentID' => TenderDocument::max('TenderDocumentID') + 1,
            'TenderID' => $tenderId,
            'FilePath' => $path,
            'Description' => $request->description,
            'CreatedBy' => Auth::id(),
        ]);

        return response()->json($document, 201);
    }

    public function show($tenderId, $documentId)
    {
        $document = TenderDocument::where('TenderID', $tenderId)
            ->where('TenderDocumentID', $documentId)
            ->with(['creator', 'modifier', 'deleter'])
            ->firstOrFail();
            
        return response()->json($document);
    }

    public function download($tenderId, $documentId)
    {
        $document = TenderDocument::where('TenderID', $tenderId)
            ->findOrFail($documentId);
            
        return Storage::download($document->FilePath, $document->originalFilename);
    }

    public function destroy($tenderId, $documentId)
    {
        $document = TenderDocument::where('TenderID', $tenderId)
            ->findOrFail($documentId);
            
        Storage::delete($document->FilePath);
        
        $document->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
        ]);
        
        return response()->json(null, 204);
    }
}