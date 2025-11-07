<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyStatusRequest;
use App\Http\Requests\ThirdPartyAuth\StoreThirdPartyRequest;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\Request;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ThirdPartyController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = ThirdParties::query()->with('types');

        if ($request->filled('type')) {
            $typeFilter = $request->input('type');
            // If numeric, assume new TypeId pivot; else fallback to legacy enum code filtering
            if (is_numeric($typeFilter)) {
                $query->whereHas('types', function ($q) use ($typeFilter) {
                    $q->where('t_ThirdPartyTypes.TypeId', $typeFilter);
                });
            } else {
                $query->where('ThirdPartyType', $typeFilter);
            }
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->input('status'));
        }

        if ($request->filled('search')) {
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

    public function store(StoreThirdPartyRequest $request): JsonResponse
    {
        try {
            $thirdParty = $this->registrationService->registerThirdPartyDetails(
                $request->user_id,
                $request->validated()
            );

            return response()->json([
                'message' => __('auth.third_party_details_submitted'),
                'third_party' => new ThirdPartyResource($thirdParty),
                'user_active_status' => ThirdPartyUser::where('UserID', $request->user_id)->first()->IsActive,
                'third_party_approval_status' => $thirdParty->ApprovalStatus->value,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Third-party submission failed: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);

            return response()->json([
                'message' => __('auth.third_party_submission_failed'),
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
            ], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        if (!$user || !$user->thirdParty || (int)$id !== (int)$user->thirdParty->Id) {
            return response()->json(['message' => 'Unauthorized access to third party profile.'], 403);
        }

        $thirdParty = ThirdParties::find($id);

        if (!$thirdParty) {
            return response()->json(['message' => 'Third party profile not found.'], 404);
        }

        return new ThirdPartyResource($thirdParty);
    }

    public function showMyThirdPartyDetails(): JsonResponse|ThirdPartyResource
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->ThirdPartyId) {
            return response()->json(['message' => 'Third-party details not found for this user.'], 404);
        }

        $thirdParty = ThirdParties::find($user->ThirdPartyId);

        if (!$thirdParty) {
            return response()->json(['message' => 'Associated third-party record not found.'], 404);
        }

        return new ThirdPartyResource($thirdParty);
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $thirdParty): ThirdPartyResource
    {
        $data = $request->validated();
        $data['ModifiedBy'] = Auth::id();

        if (array_key_exists('SupplierName', $data)) {
            $data['ThirdPartyName'] = $data['SupplierName'];
            unset($data['SupplierName']);
        }

        $thirdParty->update($data);

        return new ThirdPartyResource($thirdParty);
    }

    public function destroy(ThirdParties $thirdParty): JsonResponse
    {
        if ($thirdParty->DeletedBy === null) {
            $thirdParty->DeletedBy = Auth::id();
            $thirdParty->save();
        }
        $thirdParty->delete();

        return response()->json(null, 204);
    }

    public function getSuppliers(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $suppliers = ThirdParties::suppliers()->paginate($request->input('per_page', 15));
        return ThirdPartyResource::collection($suppliers);
    }

    public function approve(ThirdParties $thirdParty): JsonResponse
    {
        if ($thirdParty->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved) {
            return response()->json(['message' => __('auth.third_party_already_approved')], 409);
        }

        $thirdParty->ApprovalStatus = ThirdPartyApprovalStatusEnum::Approved;
        $thirdParty->ModifiedBy = Auth::id();
        $thirdParty->save();

        $thirdParty->users()->update(['IsActive' => true]);

        return (new ThirdPartyResource($thirdParty->load('users')))->response()->setStatusCode(200);
    }

    public function reject(ThirdParties $thirdParty): JsonResponse
    {
        if ($thirdParty->ApprovalStatus === ThirdPartyApprovalStatusEnum::Rejected) {
            return response()->json(['message' => __('auth.third_party_already_rejected')], 409);
        }

        $thirdParty->ApprovalStatus = ThirdPartyApprovalStatusEnum::Rejected;
        $thirdParty->ModifiedBy = Auth::id();
        $thirdParty->save();

        $thirdParty->users()->update(['IsActive' => false]);
        return (new ThirdPartyResource($thirdParty->load('users')))->response()->setStatusCode(200);
    }

    public function updateStatus(UpdateThirdPartyStatusRequest $request, ThirdParties $thirdParty): ThirdPartyResource
    {
        $thirdParty->Status = $request->validated('status');
        $thirdParty->ModifiedBy = Auth::id();
        $thirdParty->save();

        return new ThirdPartyResource($thirdParty);
    }
}
