<?php

namespace App\Http\Controllers\Web\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\StoreThirdPartyRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyRequest;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ThirdPartyWebController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = ThirdParties::query()
                ->select([
                    'Id',
                    'ThirdPartyName',
                    'Country',
                    'ThirdPartyType',
                    'ApprovalStatus',
                    'BusinessType',
                ]);

            if ($request->filled('search.value')) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ThirdPartyName', 'like', '%' . $searchTerm . '%')
                        ->orWhere('TradingName', 'like', '%' . $searchTerm . '%')
                        ->orWhere('Email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('Phone', 'like', '%' . $searchTerm . '%')
                        ->orWhere('RegistrationNumber', 'like', '%' . $searchTerm . '%')
                        ->orWhere('TaxPIN', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->filled('type')) {
                $query->where('ThirdPartyType', $request->input('type'));
            }

            if ($request->filled('status')) {
                $query->where('ApprovalStatus', $request->input('status'));
            }

            return DataTables::of($query)
                ->addColumn('ThirdPartyType', fn(ThirdParties $thirdParty) => $thirdParty->ThirdPartyType?->label() ?? 'N/A')
                ->addColumn('BusinessType', fn(ThirdParties $thirdParty) => $thirdParty->BusinessType?->label() ?? 'N/A')
                ->addColumn('ApprovalStatus', fn(ThirdParties $thirdParty) => $thirdParty->ApprovalStatus?->label() ?? $thirdParty->ApprovalStatus?->value ?? 'N/A')
                ->addColumn('actions', fn(ThirdParties $thirdParty) => '<a href="' . route('web.parties.show', ['party' => $thirdParty->Id]) . '" class="btn btn-sm btn-info">View</a>')
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('third-parties.index');
    }

    public function create(): View
    {
        return view('third-parties.create');
    }

    public function store(StoreThirdPartyRequest $request): RedirectResponse
    {
        try {
            $party = ThirdParties::create($request->validated());
            return redirect()->route('web.parties.show', ['party' => $party->Id])
                ->with('success', 'Third party created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create third party: ' . $e->getMessage(), ['request_data' => $request->all()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create third party. Please try again.');
        }
    }

    public function show(ThirdParties $party): View
    {
        return view('third-parties.show', compact('party'));
    }

    public function edit(ThirdParties $party): View
    {
        return view('third-parties.edit', compact('party'));
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $party): RedirectResponse
    {
        try {
            $party->update($request->validated());
            return redirect()->route('web.parties.show', ['party' => $party->Id])
                ->with('success', 'Third party information updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update third party: ' . $e->getMessage(), ['partyId' => $party->Id]);
            return redirect()->route('web.parties.show', ['party' => $party->Id])
                ->with('error', 'Failed to update third party information. Please try again.');
        }
    }

    public function destroy(ThirdParties $party): RedirectResponse
    {
        try {
            $party->delete();
            return redirect()->route('web.parties.index')
                ->with('success', 'Third party deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete third party: ' . $e->getMessage(), ['partyId' => $party->Id]);
            return redirect()->back()
                ->with('error', 'Failed to delete third party. Please try again.');
        }
    }
}
