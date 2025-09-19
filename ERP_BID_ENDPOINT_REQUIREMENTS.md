# 🚨 URGENT: ERP BID SUBMISSION ENDPOINT REQUIRED

## **ISSUE:** 
Portal is ready but ERP `/api/tender-bids` endpoint returns 404 Not Found.

## **ERP TEAM - IMPLEMENT THIS ENDPOINT:**

### **📍 Laravel Route Required:**
```php
// In routes/api.php
Route::post('/tender-bids', [TenderBidController::class, 'store']);
Route::get('/tender-bids', [TenderBidController::class, 'index']);
```

### **📊 Expected Request Data (FormData):**
```php
// TenderBidController@store should handle:
POST /api/tender-bids
Content-Type: multipart/form-data

Fields:
- tenderId: string
- bidAmount: string
- currency: string  
- validityPeriod: string
- deliveryPeriod: string
- paymentTerms: string
- status: string ('draft' | 'submitted')
- documents: File[]
- documentTypes: string[]
- documentEnvelopes: string[] ('technical', 'financial', 'general')
- documentValidated: string[]
- documentValidationErrors: string[]
```

### **📋 Required Database Tables:**
```sql
-- t_TenderBids table
CREATE TABLE t_TenderBids (
    BidID bigint IDENTITY(1,1) PRIMARY KEY,
    TenderID bigint NOT NULL,
    SupplierID bigint NOT NULL,
    BidAmount decimal(18,2) NOT NULL,
    Currency varchar(10) NOT NULL DEFAULT 'KES',
    ValidityPeriod int NOT NULL,
    DeliveryPeriod int NOT NULL,
    PaymentTerms nvarchar(max),
    Status varchar(20) NOT NULL DEFAULT 'draft',
    CreatedBy varchar(100),
    CreatedOn datetime2 DEFAULT GETDATE(),
    ModifiedBy varchar(100),
    ModifiedOn datetime2,
    FOREIGN KEY (TenderID) REFERENCES t_Tenders(Id),
    FOREIGN KEY (SupplierID) REFERENCES t_Suppliers(SupplierID)
);

-- t_BidDocuments table  
CREATE TABLE t_BidDocuments (
    DocumentID bigint IDENTITY(1,1) PRIMARY KEY,
    BidID bigint NOT NULL,
    DocumentName nvarchar(255) NOT NULL,
    DocumentType varchar(100) NOT NULL,
    DocumentPath nvarchar(500) NOT NULL,
    FileSize bigint,
    EncryptionKey nvarchar(500), -- For document encryption
    Envelope varchar(20) NOT NULL, -- 'technical', 'financial', 'general'
    IsValidated bit DEFAULT 0,
    ValidationErrors nvarchar(max),
    UploadedBy varchar(100),
    UploadedOn datetime2 DEFAULT GETDATE(),
    FOREIGN KEY (BidID) REFERENCES t_TenderBids(BidID)
);
```

### **📝 Sample Controller Implementation:**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenderBidController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tenderId' => 'required|string',
                'bidAmount' => 'required|numeric|min:0',
                'currency' => 'required|string',
                'validityPeriod' => 'required|integer|min:1',
                'deliveryPeriod' => 'required|integer|min:1',
                'paymentTerms' => 'nullable|string',
                'status' => 'required|in:draft,submitted',
                'documents' => 'array',
                'documents.*' => 'file|max:51200', // 50MB max
            ]);

            // Start transaction
            DB::beginTransaction();

            // Insert bid record
            $bidId = DB::table('t_TenderBids')->insertGetId([
                'TenderID' => $validated['tenderId'],
                'SupplierID' => auth()->user()->supplier_id, // Adjust based on your auth
                'BidAmount' => $validated['bidAmount'],
                'Currency' => $validated['currency'],
                'ValidityPeriod' => $validated['validityPeriod'],
                'DeliveryPeriod' => $validated['deliveryPeriod'],
                'PaymentTerms' => $validated['paymentTerms'],
                'Status' => $validated['status'],
                'CreatedBy' => auth()->user()->email ?? 'api_user',
                'CreatedOn' => now(),
            ]);

            // Handle file uploads with encryption
            if ($request->hasFile('documents')) {
                $documents = $request->file('documents');
                $documentTypes = $request->input('documentTypes', []);
                $documentEnvelopes = $request->input('documentEnvelopes', []);
                
                foreach ($documents as $index => $document) {
                    // Store file with encryption (implement your encryption logic)
                    $path = $document->store("tender-bids/{$bidId}/encrypted");
                    
                    DB::table('t_BidDocuments')->insert([
                        'BidID' => $bidId,
                        'DocumentName' => $document->getClientOriginalName(),
                        'DocumentType' => $documentTypes[$index] ?? 'other',
                        'DocumentPath' => $path,
                        'FileSize' => $document->getSize(),
                        'EncryptionKey' => $this->generateEncryptionKey(), // Implement this
                        'Envelope' => $documentEnvelopes[$index] ?? 'general',
                        'UploadedBy' => auth()->user()->email ?? 'api_user',
                        'UploadedOn' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Tender bid created successfully',
                'data' => [
                    'id' => $bidId,
                    'tenderId' => $validated['tenderId'],
                    'supplierId' => auth()->user()->supplier_id,
                    'bidAmount' => $validated['bidAmount'],
                    'currency' => $validated['currency'],
                    'status' => $validated['status'],
                    'createdOn' => now(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to create tender bid',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $tenderId = $request->input('tenderId');
        $supplierId = auth()->user()->supplier_id; // Adjust based on your auth

        $bids = DB::table('t_TenderBids')
            ->where('SupplierID', $supplierId)
            ->when($tenderId, function($query, $tenderId) {
                return $query->where('TenderID', $tenderId);
            })
            ->orderBy('CreatedOn', 'desc')
            ->get();

        return response()->json([
            'data' => $bids,
            'total' => $bids->count()
        ]);
    }

    private function generateEncryptionKey()
    {
        // Implement your encryption key generation logic
        return base64_encode(random_bytes(32));
    }
}
```

### **🔒 Security Requirements:**
1. **Document Encryption**: All uploaded documents must be encrypted at rest
2. **Access Control**: Only bid owner and procurement staff can access
3. **Audit Trail**: Log all bid submissions and modifications
4. **File Validation**: Validate file types and sizes server-side

### **📞 NEXT STEPS:**
1. **ERP Team**: Implement the above endpoint immediately
2. **Test**: Use Postman to test the endpoint directly
3. **Database**: Create the required tables
4. **Verify**: Check portal integration works after implementation

## **🎯 CURRENT STATUS:**
- ✅ **Portal Frontend**: Ready and working
- ✅ **Portal API**: Ready and working  
- ❌ **ERP Backend**: Missing `/api/tender-bids` endpoint
- ❌ **Database Tables**: May need creation

**Portal will automatically work once ERP endpoint is implemented!**
