<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderStage;
use App\Models\Procurement\TenderSupplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenderApiController extends Controller
{
    private const VISIBLE_STATUSES = [
        TenderStatusEnum::Published->value,
        TenderStatusEnum::OpeningInProgress->value,
    ];

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tender::query()->with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
            ]);

            $thirdPartyId = $this->resolveThirdPartyId($request);
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);

            // Fallback to Auth user context if available
            if (! $thirdPartyId && $user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            if ($enforceInvites) {
                // Resolve supplierId(s) for the current thirdParty (DISTINCT across multiple supplier rows)
                $supplierIds = [];
                if (! empty($thirdPartyId)) {
                    $supplierIds = DB::table('t_Suppliers')
                        ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                        ->where('t_SupplierMaster.ThirdPartyId', (int)$thirdPartyId)
                        ->whereNull('t_Suppliers.DeletedOn')
                        ->pluck('t_Suppliers.Id')
                        ->unique()
                        ->values()
                        ->all();
                } elseif ($user && method_exists($user, 'thirdParty') && $user->thirdParty) {
                    $tpId = $user->thirdParty->Id ?? null;
                    if ($tpId) {
                        $supplierIds = DB::table('t_Suppliers')
                            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                            ->where('t_SupplierMaster.ThirdPartyId', (int)$tpId)
                            ->whereNull('t_Suppliers.DeletedOn')
                            ->pluck('t_Suppliers.Id')
                            ->unique()
                            ->values()
                            ->all();
                    }
                }

                $supplierIds = array_values(array_filter(array_unique(array_map('intval', $supplierIds))));

                // Visible tenders:
                // 1) Open + visible statuses
                // 2) Restricted + invited (exists in t_TenderInvitations for any supplierId)
                $query->where(function ($vis) use ($supplierIds) {
                    $vis->where(function ($open) {
                        $open->where('TenderType', TenderTypeEnum::Open->value)
                            ->whereIn('Status', [
                                TenderStatusEnum::Published->value,
                                TenderStatusEnum::OpeningInProgress->value,
                            ]);
                    });
                    if (! empty($supplierIds)) {
                        $vis->orWhere(function ($restricted) use ($supplierIds) {
                            $restricted->where('TenderType', TenderTypeEnum::Restricted->value)
                                ->whereIn('Status', [
                                    TenderStatusEnum::Published->value,
                                    TenderStatusEnum::OpeningInProgress->value,
                                ])
                                ->where(function ($source) use ($supplierIds) {
                                    // Prefer invitations source of truth
                                    $source->whereExists(function ($sub) use ($supplierIds) {
                                        $sub->select(DB::raw(1))
                                            ->from('t_TenderInvitations as ti')
                                            ->whereColumn('ti.TenderId', 't_Tenders.Id')
                                            ->whereIn('ti.SupplierId', $supplierIds)
                                            ->whereNull('ti.DeletedOn');
                                    })
                                        // Safety: if invitations are missing, fall back to selected suppliers (t_TenderSuppliers)
                                        ->orWhereExists(function ($sub2) use ($supplierIds) {
                                            $sub2->select(DB::raw(1))
                                                ->from('t_TenderSuppliers as ts')
                                                ->whereColumn('ts.TenderID', 't_Tenders.Id')
                                                ->whereIn('ts.SupplierID', $supplierIds)
                                                ->whereNull('ts.DeletedOn');
                                        });
                                });
                        });
                    }
                });
            } else {
                // Not enforcing invites: show all visible tenders regardless of supplier (Open + Restricted)
                $query->whereIn('Status', [
                    TenderStatusEnum::Published->value,
                    TenderStatusEnum::OpeningInProgress->value,
                ]);
            }

            // Optional filters
            $statusParam = $request->query('status');
            if (! empty($statusParam)) {
                // Map user-friendly status names to database codes
                $statusMap = [
                    'Published' => TenderStatusEnum::Published->value,  // 'pb'
                    'Draft' => TenderStatusEnum::Draft->value,          // 'dr'
                    'Awarded' => TenderStatusEnum::Awarded->value,      // 'aw'
                    'Closed' => TenderStatusEnum::Closed->value,        // 'cl'
                    'OpeningInProgress' => TenderStatusEnum::OpeningInProgress->value, // 'opening_in_progress'
                ];

                // Check if it's a friendly name or already a code
                $statusCode = $statusMap[$statusParam] ?? $statusParam;
                $query->where('Status', $statusCode);
            }

            $typeParam = $request->query('tenderType');
            if (! empty($typeParam)) {
                $query->where('TenderType', $typeParam);
            }

            $search = trim((string)$request->query('search', ''));
            if ($search !== '') {
                $like = '%' . str_replace(['%', '_'], ['[%]', '[_]'], $search) . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('Title', 'like', $like)
                        ->orWhere('TenderNo', 'like', $like)
                        ->orWhere('ScopeOfWork', 'like', $like)
                        ->orWhere('Instructions', 'like', $like);
                });
            }

            $tenders = $query->orderByDesc('CreatedOn')->orderByDesc('Id')->get();

            $activity = activity()->performedOn(new Tender());
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'viewed'])
                ->log('Viewed tenders list');

            return response()->json([
                'success' => true,
                'message' => 'Tenders retrieved successfully.',
                'data' => $tenders,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to retrieve tenders. Please try again.'], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            // Get authenticated user for invitation checking
            $user = Auth::guard('sanctum')->user();
            $thirdPartyId = null;

            if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            $tender = Tender::with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
                'items.item.price',
            ])->find($id);

            if (! $tender) {
                return response()->json([
                    'message' => 'Tender not found.',
                ], 404);
            }

            // Check if user has access to this tender
            $hasAccess = false;
            $invitationStatus = null;

            // Open tenders are accessible to all authenticated users if published
            if (
                $tender->TenderType === TenderTypeEnum::Open->value &&
                in_array($tender->Status, [TenderStatusEnum::Published->value, 'opening_in_progress'])
            ) {
                $hasAccess = true;
            }

            // For restricted tenders or to get invitation status, check invitations
            if ($thirdPartyId) {
                // Get supplier IDs for this third party
                $supplierIds = DB::table('t_Suppliers')
                    ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                    ->where('t_SupplierMaster.ThirdPartyId', (int)$thirdPartyId)
                    ->whereNull('t_Suppliers.DeletedOn')
                    ->pluck('t_Suppliers.Id')
                    ->toArray();

                if (! empty($supplierIds)) {
                    // Check if supplier is invited
                    $invitation = DB::table('t_TenderInvitations')
                        ->whereIn('SupplierId', $supplierIds)
                        ->where('TenderId', $id)
                        ->whereNull('DeletedOn')
                        ->first();

                    if ($invitation) {
                        $hasAccess = true;
                        $invitationStatus = strtolower($invitation->ResponseStatus ?? 'pending');
                    }
                }
            }

            // If user doesn't have access and tender is restricted, return 403
            if (! $hasAccess && $tender->TenderType === TenderTypeEnum::Restricted->value) {
                return response()->json([
                    'message' => 'You do not have access to this tender. This is a restricted tender and you have not been invited.',
                ], 403);
            }

            $invitationStatus = $this->resolveInvitationStatus($tender->Id, $supplierIds);

            return response()->json([
                'success' => true,
                'message' => 'Tender details retrieved successfully.',
                'data' => $tenderData,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching tender details', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve tender details. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function applyVisibilityScope(Builder $query, array $supplierIds): void
    {
        $query->where(function (Builder $q) use ($supplierIds) {
            $q->where(function (Builder $open) {
                $open->where('TenderType', TenderTypeEnum::Open->value)
                    ->whereIn('Status', self::VISIBLE_STATUSES);
            });

            if (!empty($supplierIds)) {
                $q->orWhere(function (Builder $restricted) use ($supplierIds) {
                    $restricted->where('TenderType', TenderTypeEnum::Restricted->value)
                        ->whereIn('Status', self::VISIBLE_STATUSES)
                        ->whereExists(function ($sub) use ($supplierIds) {
                            $sub->selectRaw(1)
                                ->from('t_TenderInvitations as ti')
                                ->whereColumn('ti.TenderId', 't_Tenders.Id')
                                ->whereIn('ti.SupplierId', $supplierIds)
                                ->whereNull('ti.DeletedOn');
                        });
                });
            }
        });
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }

            // Add relationships one by one
            $query->with(['procurementMode', 'currency']);

            $tenders = $query->limit(10)->get();

            return response()->json([
                'message' => 'Tenders retrieved successfully.',
                'data' => $tenders,
                'debug' => [
                    'total_count' => $count,
                    'returned' => $tenders->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('TENDER API ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to retrieve tenders.',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function canAccessTender(Tender $tender, array $supplierIds): bool
    {
        if ($tender->TenderType === TenderTypeEnum::Open->value) {
            return in_array($tender->Status, self::VISIBLE_STATUSES, true);
        }

        DB::beginTransaction();

        try {
            $tender = new Tender();
            $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
            $tender->Title = $validated['title'];
            $tender->TenderType = TenderTypeEnum::from($validated['tender_type']);
            $tender->TenderCategory = $validated['tender_category_id'];
            $tender->ScopeOfWork = $validated['scope_of_work'];
            $tender->Instructions = $validated['instructions'];
            $tender->SubmissionDeadline = $validated['submission_deadline'];
            $tender->OpeningDate = $validated['opening_date'];
            $tender->Status = 'dr';
            $tender->ProcurementModeId = $validated['procurement_mode_id'];
            $tender->EstimatedValue = null;
            $tender->ItemCategoryId = $validated['item_category_id'];
            $tender->CreatedBy = Auth::id() ?? null;
            $tender->CreatedOn = now();
            $tender->ModifiedBy = Auth::id() ?? null;
            $tender->ModifiedOn = now();
            $tender->CurrencyId = $validated['currency_id'];
            $tender->save();

            $tenderId = $tender->Id;

            if (! empty($validated['plan_items'])) {
                foreach ($validated['plan_items'] as $compositeKey => $item) {
                    $split = explode('-', $compositeKey);
                    $planItemId = (int)end($split);
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'PLAN',
                        'ItemID' => $item['item_id'] ?? null,
                        'PlanItemID' => $planItemId,
                        'PlannedQty' => $item['qty'],
                        'QtyToTender' => $item['qty'],
                        'ItemCategory' => $validated['item_category_id'],
                        'Remarks' => null,
                        'RelatedPRID' => $item['pr_ref'] ?? null,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            if (! empty($validated['manual_items'])) {
                foreach ($validated['manual_items'] as $manualItem) {
                    if (empty($manualItem['item_id']) && empty($manualItem['manual_item_description'])) {
                        continue;
                    }
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'MANUAL',
                        'ItemID' => $manualItem['item_id'] ?? null,
                        'ManualItemDescription' => $manualItem['manual_item_description'] ?? null,
                        'PlannedQty' => null,
                        'QtyToTender' => $manualItem['qty'],
                        'ItemCategory' => $validated['item_category_id'],
                        'Remarks' => null,
                        'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            if (! empty($validated['suppliers'])) {
                foreach ($validated['suppliers'] as $supplierId) {
                    TenderSupplier::create([
                        'TenderID' => $tenderId,
                        'SupplierID' => $supplierId,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            $this->generateTenderStages($tender, $validated['procurement_mode_id'], $tender->CreatedOn);

            DB::commit();

            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender created successfully with ID: ' . $tenderId);

            return response()->json([
                'message' => 'Tender created successfully.',
                'tender_id' => $tenderId,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to create tender. Please try again.'], 500);
        }

        return false;
    }

    private function resolveInvitationStatus(int $tenderId, array $supplierIds): ?string
    {
        if (empty($supplierIds)) {
            return null;
        }

        $invitation = DB::table('t_TenderInvitations')
            ->where('TenderId', $tenderId)
            ->whereIn('SupplierId', $supplierIds)
            ->whereNull('DeletedOn')
            ->first();

        try {
            $tender = Tender::findOrFail($id);
            $tender->Title = $validated['title'];
            $tender->TenderCategory = $validated['tender_category_id'];
            $tender->CurrencyId = $validated['currency_id'];
            $tender->ScopeOfWork = $validated['scope_of_work'];
            $tender->Instructions = $validated['instructions'];
            $tender->SubmissionDeadline = $validated['submission_deadline'];
            $tender->OpeningDate = $validated['opening_date'];
            $tender->ProcurementModeId = $validated['procurement_mode_id'];
            $tender->ItemCategoryId = $validated['item_category_id'];
            $tender->ModifiedBy = Auth::id() ?? null;
            $tender->save();

            DB::commit();

            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'update'])
                ->log('Tender updated successfully with ID: ' . $id);

            return response()->json(['message' => 'Tender updated successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('--- UPDATE TENDER ERROR --- ' . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to update tender. Please try again.'], 500);
        }
    }

    private function resolveThirdPartyId(Request $request): ?int
    {
        if ($id = $request->query('third_party_id')) {
            return (int) $id;
        }

        DB::beginTransaction();

        try {
            $tender = Tender::findOrFail($tenderId);

            $tenderItem = TenderItems::create([
                'TenderID' => $tenderId,
                'SourceType' => ($validated['item_id'] ?? null) ? 'MANUAL' : 'MANUAL_DESCRIPTION',
                'ItemID' => $validated['item_id'] ?? null,
                'ManualItemDescription' => $validated['manual_item_description'] ?? null,
                'PlannedQty' => null,
                'QtyToTender' => $validated['qty_to_tender'],
                'ItemCategory' => $tender->ItemCategoryId,
                'Remarks' => null,
                'RelatedPRID' => $validated['pr_ref'] ?? null,
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            DB::commit();

            $activity = activity()->performedOn($tenderItem);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender Item added successfully to Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender item added successfully.', 'item' => $tenderItem], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- ADD TENDER ITEM ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to add tender item. Please try again.'], 500);
        }
    }

    private function resolveSupplierIds(?int $thirdPartyId): array
    {
        try {
            DB::beginTransaction();
            $tenderItem = TenderItems::where('TenderID', $tenderId)->findOrFail($itemId);
            $tenderItem->delete();

            DB::commit();

            $activity = activity()->performedOn($tenderItem);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender Item deleted successfully from Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender item deleted successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- DELETE TENDER ITEM ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to delete tender item. Please try again.'], 500);
        }
    }

    public function addSupplier(Request $request, string $tenderId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $tender = Tender::findOrFail($tenderId);

            $tenderSupplier = TenderSupplier::create([
                'TenderID' => $tenderId,
                'SupplierID' => $validated['supplier_id'],
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            DB::commit();

            $activity = activity()->performedOn($tenderSupplier);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender Supplier added successfully to Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender supplier added successfully.', 'supplier' => $tenderSupplier], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- ADD TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to add tender supplier. Please try again.'], 500);
        }
    }

    public function deleteSupplier(string $tenderId, string $supplierId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $tenderSupplier = TenderSupplier::where('TenderID', $tenderId)->findOrFail($supplierId);
            $tenderSupplier->delete();

            DB::commit();

            $activity = activity()->performedOn($tenderSupplier);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender Supplier deleted successfully from Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender supplier deleted successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- DELETE TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json(['message' => 'Failed to delete tender supplier. Please try again.'], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tender = Tender::findOrFail($id);
            $tender->delete();
            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender deleted successfully with ID: ' . $id);

            return response()->json(['message' => 'Tender deleted successfully.'], 200);
        } catch (Throwable $th) {
            Log::error("--- DELETE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);

            return response()->json(['message' => 'Failed to delete tender. Please try again.'], 500);
        }
    }

    protected function generateTenderStages(Tender $tender, $procurementModeId, $startDate)
    {
        $timelineStages = ModeTimeline::where('ProcurementModeId', $procurementModeId)->get();
        $startDate = Carbon::parse($startDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate->toDateString(),
                'EndDate' => $endDate->toDateString(),
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);
            $startDate = (clone $endDate)->addDay();
        }
    }
}
