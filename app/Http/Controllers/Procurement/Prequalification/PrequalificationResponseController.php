<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrequalificationResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $responses = PrequalificationResponse::with(['application', 'criteria', 'createdBy'])
            ->when($request->get('application_id'), fn($q, $id) => $q->where('ApplicationID', $id))
            ->when($request->get('round_id'), fn($q, $id) => $q->whereHas('criteria', fn($q2) => $q2->where('RoundId', $id)))
            ->paginate(20);

        return response()->json($responses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ApplicationID' => ['required', 'integer', 'exists:t_SupplierPrequalificationApplications,ApplicationID'],
            'Responses' => ['required', 'array'],
            'Responses.*.CriteriaId' => ['required', 'integer', 'exists:t_PrequalificationRoundCriteria,Id'],
            'Responses.*.ResponseText' => ['nullable', 'string'],
            'Responses.*.ResponseFile' => ['nullable', 'string'],
            'Responses.*.ResponseBoolean' => ['nullable', 'boolean'],
        ]);

        $responses = DB::transaction(function () use ($validated) {
            $results = [];
            foreach ($validated['Responses'] as $r) {
                $r['ApplicationID'] = $validated['ApplicationID'];
                $r['CreatedBy'] = auth()->id();
                $results[] = PrequalificationResponse::create($r);
            }
            return $results;
        });

        return response()->json($responses, 201);
    }

    public function show(PrequalificationResponse $prequalificationResponse): JsonResponse
    {
        return response()->json($prequalificationResponse->load(['application', 'criteria', 'createdBy']));
    }

    public function update(Request $request, PrequalificationResponse $prequalificationResponse): JsonResponse
    {
        $validated = $request->validate([
            'ResponseText' => ['nullable', 'string'],
            'ResponseFile' => ['nullable', 'string'],
            'ResponseBoolean' => ['nullable', 'boolean'],
        ]);

        $validated['ModifiedBy'] = auth()->id();

        $prequalificationResponse->update($validated);

        return response()->json($prequalificationResponse->load(['application', 'criteria', 'createdBy']));
    }

    public function destroy(PrequalificationResponse $prequalificationResponse): JsonResponse
    {
        $prequalificationResponse->DeletedBy = auth()->id();
        $prequalificationResponse->save();
        $prequalificationResponse->delete();

        return response()->json(['message' => 'Response deleted successfully']);
    }
}
