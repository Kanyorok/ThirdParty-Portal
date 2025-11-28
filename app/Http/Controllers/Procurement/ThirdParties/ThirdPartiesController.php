<?php

namespace App\Http\Controllers\Procurement\ThirdParties;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdParties;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use Illuminate\Http\Request;

class ThirdPartiesController extends Controller
{
    public function index(Request $request)
    {
        $query = ThirdParties::query()->with(['types', 'country']);

        if ($request->has('type')) {
            $type = strtoupper($request->type);
            $query->where('ThirdPartyType', $type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ThirdPartyName', 'like', "%{$search}%")
                    ->orWhere('TradingName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $thirdParties = $query->paginate(25);

        return ThirdPartyResource::collection($thirdParties);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ThirdPartyName' => 'required|string|max:255',
            'Email' => 'required|email|unique:t_ThirdParties,Email',
            'ThirdPartyType' => 'required|in:S,T,C',
            'types_ids' => 'nullable|array',
            'types_ids.*' => 'exists:t_ThirdPartyTypes,TypeId',
            'RegistrationNumber' => 'nullable|string|max:100',
            'CountryId' => 'nullable|exists:t_Countries,Id',
        ]);

        $thirdParty = ThirdParties::create($validated);

        if (isset($validated['types_ids'])) {
            $thirdParty->types()->sync($validated['types_ids']);
        }

        return new ThirdPartyResource($thirdParty->load(['types', 'country']));
    }

    public function show(ThirdParties $thirdParty)
    {
        return new ThirdPartyResource($thirdParty->load(['types', 'country']));
    }

    public function update(Request $request, ThirdParties $thirdParty)
    {
        $validated = $request->validate([
            'ThirdPartyName' => 'sometimes|required|string|max:255',
            'TradingName' => 'nullable|string|max:255',
            'Email' => 'sometimes|required|email|unique:t_ThirdParties,Email,' . $thirdParty->Id . ',Id',
            'types_ids' => 'nullable|array',
            'types_ids.*' => 'exists:t_ThirdPartyTypes,TypeId',
            'RegistrationNumber' => 'nullable|string|max:100',
            'CountryId' => 'nullable|exists:t_Countries,Id',
        ]);

        $thirdParty->update($validated);

        if (isset($validated['types_ids'])) {
            $thirdParty->types()->sync($validated['types_ids']);
        }

        return new ThirdPartyResource($thirdParty->load(['types', 'country']));
    }

    public function destroy(ThirdParties $thirdParty)
    {
        $thirdParty->delete();
        return response()->noContent();
    }
}
