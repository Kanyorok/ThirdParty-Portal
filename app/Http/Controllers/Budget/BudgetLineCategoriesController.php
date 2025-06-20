<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetLineCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetLineCategoriesController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetLineCategories::class);
        $budgetLineCategories = BudgetLineCategories::all();
        // Logic to show budget line categories
        return view('budgetandanalytics.settings.budgetlinecategories.index', compact('budgetLineCategories'));
    }

    public function create()
    {
        // Logic to show form for creating a new budget line category
        return view('budgetandanalytics.settings.budgetlinecategories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CategoryCode' => 'required|string|max:255',
            'CategoryName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IsActive' => 'boolean', // Optional, default is true
        ]);

        DB::beginTransaction();

        try {
            $budgetLineCategory = BudgetLineCategories::create([
                'CategoryCode' => $validated['CategoryCode'],
                'CategoryName' => $validated['CategoryName'],
                'Description' => $validated['Description'],
                'IsActive' => $validated['IsActive'] ?? true, // Default to true if not provided
                'CreatedBy' =>Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);
            DB::commit();
            activity()
                ->performedOn($budgetLineCategory)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created budget line category: ' . $budgetLineCategory->CategoryName);
            return redirect()->route('budgetlinecategories.index')->with('success', 'Budget Line Category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Budget Line Category: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to create Budget Line Category: ' . $e->getMessage()]);
        }
    }

}

