<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\AssetBook;
use Illuminate\Http\Request;

class AssetBookController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $books = AssetBook::when($q, fn ($qq) =>
                $qq->where('Code', 'like', "%$q%")->orWhere('Name', 'like', "%$q%"))
            ->orderBy('Name')->paginate(20);

        return view('assets.settings.books.index', compact('books', 'q'));
    }

    public function create()
    {
        return view('assets.settings.books.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|max:20|unique:t_AssetBooks,Code',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        AssetBook::create($data);

        return redirect()->route('assets.settings.books.index')->with('success', 'Book created.');
    }

    public function edit(int $id)
    {
        $book = AssetBook::findOrFail($id);

        return view('assets.settings.books.edit', compact('book'));
    }

    public function update(Request $request, int $id)
    {
        $book = AssetBook::findOrFail($id);
        $data = $request->validate([
            'Code' => 'required|max:20|unique:t_AssetBooks,Code,' . $book->Id . ',Id',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        $book->update($data);

        return redirect()->route('assets.settings.books.index')->with('success', 'Book updated.');
    }

    public function destroy(int $id)
    {
        AssetBook::where('Id', $id)->delete();

        return redirect()->route('assets.settings.books.index')->with('success', 'Book deleted.');
    }
}
