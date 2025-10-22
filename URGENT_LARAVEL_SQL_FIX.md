# 🚨 **URGENT: Laravel SQL Server Parameter Binding Fix**

## 🎯 **THE PROBLEM**

Laravel is generating unquoted SQL for SQL Server:

```sql
❌ BAD:  UPDATE t_TenderInvitations SET ResponseStatus = accepted WHERE InvitationID = 3
✅ GOOD: UPDATE t_TenderInvitations SET ResponseStatus = 'accepted' WHERE InvitationID = 3
```

## 🔧 **SOLUTION 1: Fix TenderInvitation Model (RECOMMENDED)**

**File:** `app/Models/Procurement/TenderInvitation.php`

Add this to your model class:

```php
<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class TenderInvitation extends Model
{
    protected $table = 't_TenderInvitations';
    protected $primaryKey = 'InvitationID';
    
    // Fix Laravel SQL Server parameter binding
    protected $casts = [
        'ResponseStatus' => 'string',  // Force string casting
        'DeclineReason' => 'string',
        'ModifiedBy' => 'string',
    ];
    
    protected $fillable = [
        'TenderId',
        'SupplierId', 
        'InvitationDate',
        'ResponseStatus',
        'ResponseDate',
        'DeclineReason',
        'ConfirmationAttachment',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy', 
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    // Ensure dates are handled properly
    protected $dates = [
        'InvitationDate',
        'ResponseDate', 
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn'
    ];

    public $timestamps = false; // We're using custom timestamp fields
}
```

## 🔧 **SOLUTION 2: Alternative Controller Fix**

**File:** `app/Http/Controllers/Procurement/TenderInvitationController.php`

Replace the `update` method with this:

```php
public function update(Request $request, $id): JsonResponse
{
    try {
        $validated = $request->validate([
            'responseStatus' => 'required|in:accepted,declined,pending',
            'declineReason' => 'nullable|string|required_if:responseStatus,declined',
        ]);

        // Use Query Builder instead of Eloquent for better SQL Server control
        $affected = DB::table('t_TenderInvitations')
            ->where('InvitationID', $id)
            ->update([
                'ResponseStatus' => (string) $validated['responseStatus'], // Explicit cast
                'ResponseDate' => now()->format('Y-m-d H:i:s'),
                'DeclineReason' => $validated['declineReason'] ? (string) $validated['declineReason'] : null,
                'ModifiedBy' => auth()->id() ? (string) auth()->id() : 'api_user',
                'ModifiedOn' => now()->format('Y-m-d H:i:s'),
            ]);

        if ($affected === 0) {
            return response()->json([
                'error' => 'Invitation not found or no changes made'
            ], 404);
        }

        // Fetch updated record
        $invitation = DB::table('t_TenderInvitations')
            ->where('InvitationID', $id)
            ->first();

        Log::info('Tender invitation response updated', [
            'invitation_id' => $id,
            'response_status' => $validated['responseStatus'],
            'affected_rows' => $affected
        ]);

        return response()->json([
            'message' => 'Invitation response updated successfully',
            'data' => [
                'InvitationID' => $invitation->InvitationID,
                'ResponseStatus' => $invitation->ResponseStatus,
                'ResponseDate' => $invitation->ResponseDate,
                'DeclineReason' => $invitation->DeclineReason,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating tender invitation', [
            'invitation_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'Failed to update invitation response',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

Don't forget to add this import at the top:

```php
use Illuminate\Support\Facades\DB;
```

## 🧪 **TEST THE FIX**

```bash
curl -X PUT "http://localhost:8000/api/tender-invitations/3" \
  -H "Content-Type: application/json" \
  -d '{"responseStatus": "accepted"}'
```

**Expected Response:**

```json
{
  "message": "Invitation response updated successfully",
  "data": {
    "InvitationID": 3,
    "ResponseStatus": "accepted", 
    "ResponseDate": "2024-09-17 15:30:00",
    "DeclineReason": null
  }
}
```

## 🚀 **QUICK STEPS**

1. **OPTION A (Recommended):** Add the model configuration above
2. **OPTION B (Alternative):** Replace the controller method
3. **Test:** Run the curl command
4. **Verify:** Check your database - ResponseStatus should be 'accepted'

## 💡 **WHY THIS FIXES IT**

- **Explicit string casting** forces Laravel to quote string values
- **Query Builder** gives more control over SQL generation than Eloquent
- **Manual date formatting** ensures proper SQL Server date handling
- **Proper fillable/casts** tells Laravel how to handle each field

**⏱️ Time needed: 5-10 minutes**

## 🆘 **IF STILL NOT WORKING**

Check your `config/database.php` SQL Server connection:

```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    // ... other config
    'options' => [
        PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 60,
        PDO::ATTR_STRINGIFY_FETCHES => true,  // Add this line
    ],
],
```

This forces PDO to quote all values properly for SQL Server.
