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
        DB::unprepared("
CREATE   PROC p_GetClientStatement

(

    @ThirdPartyID INT,
    @FromDate DATE = NULL,
    @ToDate DATE = NULL
)

AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        -- Validate ThirdParty ID exists
        IF NOT EXISTS (SELECT 1 FROM t_ThirdParties WITH (NOLOCK) WHERE Id = @ThirdPartyID)
        BEGIN
            RAISERROR('ThirdParty ID %d does not exist.', 16, 1, @ThirdPartyID);
            RETURN;
        END

        -- Validate date range
        IF (@FromDate IS NOT NULL AND @ToDate IS NOT NULL AND @FromDate > @ToDate)
        BEGIN
            RAISERROR('FromDate cannot be greater than ToDate.', 16, 1);
            RETURN;
        END


        -- Temporary tables
        DROP TABLE IF EXISTS #ClientStatement;


        -- Get client account information
        DECLARE @ClientName  NVARCHAR(255);
        DECLARE @AccountNumber NVARCHAR(100);
        DECLARE @OpeningBalance DECIMAL(18,2) = 0;

        SELECT 
            @ClientName = TP.ThirdPartyName,
            @AccountNumber = FT.ReferenceNumber
        FROM t_ThirdParties TP WITH (NOLOCK)
        JOIN t_FinancialTransactions FT WITH (NOLOCK) ON TP.Id = FT.ThirdPartyID
        WHERE TP.Id = @ThirdPartyID;

        -- Calculate opening balance (balance before FromDate)
        IF @FromDate IS NOT NULL
        BEGIN
            SELECT @OpeningBalance = ISNULL(SUM(FT.Amount), 0)
            FROM t_FinancialTransactions FT WITH (NOLOCK)
            WHERE FT.ThirdPartyID = @ThirdPartyID
              AND FT.TransactionDate < @FromDate;
        END

        -- Get all transactions for that client with running balance
        SELECT 
            FT.Id AS TransactionID,
            FT.ThirdPartyID,
            FT.TransactionDate,
            FT.Narration AS Description,
            FT.TransactionType,
            CASE WHEN FT.Amount < 0 THEN ABS(FT.Amount) ELSE 0 END AS Debit,
            CASE WHEN FT.Amount > 0 THEN ABS(FT.Amount) ELSE 0 END AS Credit,
            FT.CurrencyCode,
            FT.Amount,
            SUM(FT.Amount) OVER (
                PARTITION BY FT.ThirdPartyID 
                ORDER BY FT.TransactionDate, FT.Id
                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
            ) + @OpeningBalance AS RunningBalance,
           

            @ClientName AS ClientName,
            @AccountNumber AS AccountNumber         
        INTO #ClientStatement
        FROM t_FinancialTransactions FT WITH (NOLOCK)
        WHERE FT.ThirdPartyID = @ThirdPartyID
          AND (@FromDate IS NULL OR FT.TransactionDate >= @FromDate)
          AND (@ToDate IS NULL OR FT.TransactionDate < DATEADD(DAY, 1, @ToDate))    
        ORDER BY FT.TransactionDate, FT.Id; 
        -- Show final statement
        SELECT 
            TransactionID,
            TransactionDate,
            Description,
            Debit,
            Credit,
            CurrencyCode,
            Amount,
            RunningBalance
        FROM #ClientStatement
        ORDER BY TransactionDate, TransactionID;

        -- Cleanup
        DROP TABLE IF EXISTS #ClientStatement;

    END TRY
    BEGIN CATCH
        -- Cleanup on error
        DROP TABLE IF EXISTS #ClientStatement;
        
        -- Return error information
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
        DECLARE @ErrorSeverity INT = ERROR_SEVERITY();
        DECLARE @ErrorState INT = ERROR_STATE();
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    END CATCH
END;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_GetClientStatement");
    }
};
