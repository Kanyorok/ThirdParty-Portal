<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use Illuminate\Http\Request;

class DocumentMoveController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Document $document)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Document $document)
    {
        /*$this->authorize('move', $document);

        // Validate the request
        $request->validate([
            'repository' => 'required|exists:repositories,id',
        ]);

        DB::transaction(function () use ($request, $document) {
            $document->update([
                'repository_id' => $request->repository]
            );

            activity()->causedBy($actor)->performedOn($this->document)->event('upload')->log('relation added');
        })

        // Move the document to the specified repository
        $document->moveToRepository($request->input('repository_id'));

        return redirect()->route('repo.files.index', ['repository' => $document->repository_id])
                         ->with('success', 'Document moved successfully.');*/
    }
}
