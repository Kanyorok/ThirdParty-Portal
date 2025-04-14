<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ItemCategory;

class ItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = ItemCategory::all();
        return view('procurement.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('procurement.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);


        ItemCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'CreatedBy' => auth()->user()->Id, 
            'ModifiedBy' => auth()->user()->Id, 
        ]);

        return redirect()->route('procurement.categories.index')->with('success', 'Category created successfully.');
    }
}