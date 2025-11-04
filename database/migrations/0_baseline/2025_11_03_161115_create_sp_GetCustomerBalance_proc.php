<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("-- Create stored procedure for getting customer balance
CREATE PROCEDURE sp_GetCustomerBalance
    @CustomerId INT
AS
BEGIN
    SET NOCOUNT ON;

    -- Error handling
    IF NOT EXISTS (SELECT 1 FROM t_Customers WHERE CustomerId = @CustomerId)
    BEGIN
        RAISERROR ('Customer does not exist.', 16, 1)
        RETURN
    END

    -- Get customer balance by summing all financial transactions
    SELECT 
        c.CustomerId,
        c.CustomerName,
        ISNULL(SUM(CASE 
            WHEN ft.TransactionType = 'CREDIT' THEN ft.Amount
            WHEN ft.TransactionType = 'DEBIT' THEN -ft.Amount
            ELSE 0
        END), 0) AS CurrentBalance
    FROM 
        t_Customers c
    LEFT JOIN 
        t_FinancialTransactions ft ON c.CustomerId = ft.CustomerId
    WHERE 
        c.CustomerId = @CustomerId
    GROUP BY 
        c.CustomerId,
        c.CustomerName;
END;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_GetCustomerBalance");
    }
};
