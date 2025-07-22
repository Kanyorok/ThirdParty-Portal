<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\UpdateThirdPartyRequest;
use App\Http\Requests\ThirdParty\UpdateThirdPartyStatusRequest;
use App\Http\Requests\ThirdPartyAuth\StoreThirdPartyRequest as ThirdPartyAuthStoreThirdPartyRequest;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\Request;

class ThirdPartyController extends Controller
{
    public function index(Request $request)
    {
        $query = ThirdParties::query();

        if ($request->has('type')) {
            $query->where('ThirdPartyType', $request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('Status', $request->input('status'));
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('ThirdPartyName', 'like', $searchTerm)
                    ->orWhere('TradingName', 'like', $searchTerm)
                    ->orWhere('Email', 'like', $searchTerm)
                    ->orWhere('Phone', 'like', $searchTerm);
            });
        }

        $thirdParties = $query->paginate($request->input('per_page', 15));

        return ThirdPartyResource::collection($thirdParties);
    }

    public function store(ThirdPartyAuthStoreThirdPartyRequest $request)
    {
        $data = $request->validated();
        $data['CreatedBy'] = auth()->id();

        if (isset($data['SupplierName'])) {
            $data['ThirdPartyName'] = $data['SupplierName'];
        }

        $thirdParty = ThirdParties::create($data);

        return new ThirdPartyResource($thirdParty);
    }

    public function show(ThirdParties $thirdParty)
    {
        return new ThirdPartyResource($thirdParty);
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $thirdParty)
    {
        $data = $request->validated();
        $data['ModifiedBy'] = auth()->id();

        if (isset($data['SupplierName'])) {
            $data['ThirdPartyName'] = $data['SupplierName'];
        }

        $thirdParty->update($data);

        return new ThirdPartyResource($thirdParty);
    }

    public function destroy(ThirdParties $thirdParty)
    {
        $thirdParty->DeletedBy = auth()->id();
        $thirdParty->save();
        $thirdParty->delete();

        return response()->json(null, 204);
    }

    public function getSuppliers(Request $request)
    {
        $suppliers = ThirdParties::suppliers()->paginate($request->input('per_page', 15));
        return ThirdPartyResource::collection($suppliers);
    }

    public function approve(ThirdParties $thirdParty)
    {
        if ($thirdParty->ApprovalStatus === 'Approved') {
            return response()->json(['message' => __('auth.user_already_approved')], 409);
        }

        $thirdParty->ApprovalStatus = 'Approved';
        $thirdParty->ModifiedBy = auth()->id();
        $thirdParty->save();

        return new ThirdPartyResource($thirdParty);
    }

    public function reject(ThirdParties $thirdParty)
    {
        if ($thirdParty->ApprovalStatus === 'Rejected') {
            return response()->json(['message' => __('auth.user_already_rejected')], 409);
        }

        $thirdParty->ApprovalStatus = 'Rejected';
        $thirdParty->ModifiedBy = auth()->id();
        $thirdParty->save();

        return new ThirdPartyResource($thirdParty);
    }

    public function updateStatus(UpdateThirdPartyStatusRequest $request, ThirdParties $thirdParty)
    {
        $thirdParty->Status = $request->validated('status');
        $thirdParty->ModifiedBy = auth()->id();
        $thirdParty->save();

        return new ThirdPartyResource($thirdParty);
    }
}
