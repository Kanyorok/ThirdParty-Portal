# 🚀 **Backend Activation - 5 Minute Fix**

## **Step 1: Activate Tender API Routes**

In `routes/api.php`, **uncomment these lines** (around line 29):

```php
// CHANGE FROM (commented out):
// Route::get('/tenders', [TenderApiController::class, 'index']);
// Route::post('/tenders', [TenderApiController::class, 'store']);
// Route::put('/tenders/{id}', [TenderApiController::class, 'update']);
// Route::delete('/tenders/{id}', [TenderApiController::class, 'destroy']);

// TO (uncommented):
Route::get('/tenders', [TenderApiController::class, 'index']);
Route::post('/tenders', [TenderApiController::class, 'store']);  
Route::put('/tenders/{id}', [TenderApiController::class, 'update']);
Route::delete('/tenders/{id}', [TenderApiController::class, 'destroy']);
```

## **Step 2: Add Missing Imports**

Add these imports to the top of `routes/api.php`:

```php
use App\Http\Controllers\Procurement\TenderApiController;
use App\Http\Controllers\Procurement\TenderInvitationController;
```

## **Step 3: Add Supplier-Specific Routes**

Add these routes inside the `Route::middleware('auth:sanctum')->group()` section:

```php
// Supplier Tender Management
Route::prefix('tender-invitations')->group(function () {
    Route::get('/', [TenderInvitationController::class, 'getSupplierInvitations']);
    Route::put('/{id}', [TenderInvitationController::class, 'updateInvitationResponse']);
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
```

## **Step 4: Add Missing Controller Methods**

Add these methods to `TenderInvitationController.php`:

```php
public function getSupplierInvitations(Request $request)
{
    $supplierId = $request->query('supplier_id');
    
    $invitations = TenderInvitation::with(['tender' => function($query) {
            $query->with(['currency', 'procurementMode', 'tenderCategoryRelation']);
        }])
        ->where('SupplierId', $supplierId)
        ->orderBy('InvitationDate', 'desc')
        ->get();
    
    return response()->json([
        'data' => $invitations,
        'total' => $invitations->count()
    ]);
}

public function updateInvitationResponse(Request $request, $id)
{
    $validated = $request->validate([
        'ResponseStatus' => 'required|in:accepted,declined',
        'DeclineReason' => 'nullable|string',
    ]);

    $invitation = TenderInvitation::findOrFail($id);
    
    $invitation->update([
        'ResponseStatus' => $validated['ResponseStatus'],
        'ResponseDate' => now(),
        'DeclineReason' => $validated['DeclineReason'] ?? null,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now(),
    ]);

    return response()->json([
        'message' => 'Invitation response updated successfully',
        'data' => $invitation
    ]);
}
```

## **Step 5: Test the Connection**

1. **Start your Laravel backend server:**
   ```bash
   php artisan serve
   ```

2. **Start your Next.js frontend:**
   ```bash
   cd ThirdParty-Portal
   npm run dev
   ```

3. **Test the connection:**
   - Login to your frontend at `http://localhost:3000`
   - Visit: `http://localhost:3000/api/test-connection`
   - Should show: `"status": "backend_connected"`

## **Step 6: Verify Everything Works**

1. **Navigate to Tenders page** - Should show real data from database
2. **Click "View Details"** on any tender - Modal should open with all tabs
3. **Test invitation responses** - Accept/decline should work
4. **Check database** - t_TenderInvitations table should update

---

## **🎉 Result: Complete Working System**

After these 5 minutes of changes:
- ✅ Frontend connects to real backend data
- ✅ Suppliers can view actual tenders from t_Tenders table
- ✅ Invitation responses update t_TenderInvitations table  
- ✅ Full tender workflow operational
- ✅ Email invitations working (already implemented)

**Total Time Required: ~5 minutes of uncommenting and adding a few methods!**
