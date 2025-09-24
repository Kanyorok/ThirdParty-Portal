# Credit Management GL Integration

## Overview

The Credit Management module now integrates with the Finance GL (General Ledger) system, automatically creating journal entries and updating GL balances when credit transactions occur. This follows the same pattern as the Property Invoice Service integration.

## System Architecture

### GL Mapping Configuration

The system uses the `t_FinanceGlTransactionsMapping` table to map credit management transactions to appropriate GL accounts:

```sql
-- From FinanceGlTransactionsMappingSeeder.php
TransactionTypeID: 21
DebitGLAccountID: 2   -- Accounts Receivable
CreditGLAccountID: 4  -- Accounts Payable
ModuleID: 1100000     -- Finance Module
```

### Transaction Flow

1. **Credit Profile Approval** → Creates initial GL entries
2. **Credit Adjustments** → Updates GL balances when approved
3. **Credit Utilization** → Records customer usage of credit
4. **Credit Payments** → Records customer payments against credit
5. **Transaction Reversals** → Handles cancellations and corrections

## Implementation Details

### Service Layer: `CreditManagementTransactionService`

This service handles all GL postings for credit management operations:

#### Key Methods:

1. **`postCreditApproval()`** - Initial credit approval GL posting
2. **`postCreditAdjustment()`** - Credit increase/decrease/revision GL posting
3. **`postCreditUtilization()`** - Customer credit usage GL posting
4. **`postCreditPayment()`** - Customer payment against credit GL posting
5. **`reverseCreditTransaction()`** - Reversal of any credit transaction

### Controller Integration

Both `CreditManagementController` and `CreditAdjustmentController` have been updated to automatically call the transaction service when approvals occur.

#### Credit Profile Approval Flow:
```php
// In CreditManagementController::approve()
$credit->update(['ApprovalStatus' => 'approved']);

// Create movement record
FinanceCreditMovement::create([...]);

// Post GL transactions
$transactionService = app(CreditManagementTransactionService::class);
$glResult = $transactionService->postCreditApproval($credit, $reason);
```

#### Credit Adjustment Approval Flow:
```php
// In CreditAdjustmentController::approve()
$adjustment->update(['ApprovalStatus' => 'approved']);
$creditProfile->update(['CreditLimit' => $newLimit]);

// Create movement record
FinanceCreditMovement::create([...]);

// Post GL transactions
$glResult = $transactionService->postCreditAdjustment($adjustment);
```

## Journal Entry Examples

### Credit Profile Approval
```
DR  Accounts Receivable    KES 100,000.00
    CR  Accounts Payable                   KES 100,000.00
Narration: Credit limit approved for ABC Company Ltd: Initial approval
```

### Credit Increase
```
DR  Accounts Receivable    KES 50,000.00
    CR  Accounts Payable                   KES 50,000.00
Narration: Credit increase for ABC Company Ltd: Business expansion
```

### Credit Utilization
```
DR  Accounts Receivable    KES 25,000.00
    CR  Accounts Payable                   KES 25,000.00
Narration: Credit utilization - ABC Company Ltd: Invoice INV-001
```

### Credit Payment
```
DR  Accounts Payable       KES 25,000.00
    CR  Accounts Receivable                KES 25,000.00
Narration: Credit payment - ABC Company Ltd: Payment against credit
```

## Key Features

### 1. Idempotency Protection
- Each transaction uses a unique `IdempotencyKey`
- Prevents duplicate postings if the same operation is triggered multiple times
- Format: `credit_approval_{credit_id}`, `credit_adjustment_{adjustment_id}`, etc.

### 2. Audit Trail
- Complete activity logging using Laravel's activity log
- GL transaction batch numbers linked to credit operations
- Detailed movement tracking in `t_FinanceCreditMovements`

### 3. Error Handling
- GL posting failures don't prevent credit approval (logged as warnings)
- Graceful degradation - credit management continues to work even if GL system is unavailable
- Comprehensive error logging for troubleshooting

### 4. Reversal Support
- Ability to reverse any credit transaction
- Automatically creates offsetting journal entries
- Maintains complete audit trail of all changes

## Database Tables Involved

### Primary Tables:
- `t_FinanceCreditManagement` - Credit profiles
- `t_FinanceCreditAdjustments` - Credit adjustments
- `t_FinanceCreditMovements` - Movement history

### GL Integration Tables:
- `t_FinanceGlTransactionsMapping` - Transaction type mappings
- `t_FinanceGLTransactions` - Individual GL transactions
- `t_FinanceJournalEntry` - Journal entry headers
- `t_FinanceJournalEntryLines` - Journal entry lines
- `t_FinanceGLBranch` - GL account balances

## Configuration

### Required GL Mappings
Ensure the following mapping exists in `t_FinanceGlTransactionsMapping`:

```sql
INSERT INTO t_FinanceGlTransactionsMapping (
    ModuleID, TransactionTypeID, DebitGLAccountID, CreditGLAccountID, IsActive
) VALUES (
    1100000, 21, 2, 4, 1
);
```

### GL Account Structure
- **Debit GL (2)**: Accounts Receivable - Increases when credit is granted
- **Credit GL (4)**: Accounts Payable - Balancing account for credit transactions

## Monitoring and Troubleshooting

### Activity Logs
All GL postings are logged with the following information:
- Action type (credit_approval_posted, credit_adjustment_posted, etc.)
- Transaction batch number
- Amount posted
- Reference numbers

### Error Monitoring
Monitor application logs for:
- `Credit approval GL posting failed`
- `Credit adjustment GL posting failed`
- `Credit transaction reversal failed`

### GL Balance Verification
Regular reconciliation between:
- Total approved credit limits in `t_FinanceCreditManagement`
- GL balances in `t_FinanceGLBranch` for credit-related accounts

## Testing

### Test Scenarios:
1. **Create and approve new credit profile** → Verify GL entries created
2. **Create and approve credit adjustment** → Verify GL balances updated
3. **Simulate GL system failure** → Verify credit approval continues with warning
4. **Test idempotency** → Verify duplicate requests don't create duplicate entries
5. **Test reversals** → Verify offsetting entries are created correctly

### Test Data
Use the credit adjustment create form with real-time preview to test calculations before approval.

## Integration Points

### Existing Integrations:
- **Property Invoice Service** → Similar pattern for rent invoices
- **Journal Entry System** → Uses same approval workflow
- **TransactionService** → Core GL posting engine

### Future Integrations:
- **Accounts Receivable** → Link credit utilization to customer invoices
- **Cash Management** → Link credit payments to bank receipts
- **Reporting** → Credit aging and utilization reports

## Best Practices

1. **Always test GL mappings** before deploying to production
2. **Monitor GL posting success rates** via application logs
3. **Reconcile credit balances** with GL balances regularly
4. **Use idempotency keys** for all external integrations
5. **Implement proper error handling** for all GL operations
6. **Maintain audit trails** for all credit transactions

## Conclusion

The Credit Management GL integration provides:
- ✅ Automatic journal entry creation
- ✅ Real-time GL balance updates
- ✅ Complete audit trails
- ✅ Error resilience
- ✅ Idempotency protection
- ✅ Reversal capabilities

This integration ensures that all credit management operations are properly reflected in the financial system while maintaining data integrity and providing comprehensive audit capabilities.

