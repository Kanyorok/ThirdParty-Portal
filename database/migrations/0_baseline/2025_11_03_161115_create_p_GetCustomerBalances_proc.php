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
        DB::unprepared("CREATE   PROC p_GetCustomerBalances
AS
BEGIN

    SET NOCOUNT ON;

    DROP TABLE IF EXISTS #FinanceCustomerBalances;
    
    WITH CustomerBalances AS (
        SELECT
            tp.id AS ThirdPartyID,
            tpt.[Type] AS ThirdPartyTypeID,
            ft.CurrencyCode,
            MAX(ft.Id) AS LastTransactionID,
            CAST(SUM(COALESCE(ft.Amount,0)) AS numeric(18,2)) AS TotalAmount,
            CAST(SUM(
                CASE 
                    WHEN ft.CurrencyID = 56 THEN COALESCE(ft.Amount,0)
                    ELSE COALESCE(ft.Amount,0) * COALESCE(ft.ExchangeRate,0)
                END
            ) AS numeric(18,2)) AS TotalLocal,
            CAST(SUM(
                CASE 
                    WHEN ft.CurrencyID <> 56 THEN COALESCE(ft.Amount,0)
                    ELSE COALESCE(ft.Amount,0) / NULLIF(COALESCE(ft.ExchangeRate,0),0)
                END
            ) AS numeric(18,2)) AS TotalForeign
            
        FROM t_FinancialTransactions ft WITH (NOLOCK)
        INNER JOIN t_ThirdParties tp WITH (NOLOCK) ON ft.ThirdPartyID = tp.Id
        LEFT JOIN t_ThirdPartyTypes tpt WITH (NOLOCK) ON tp.ThirdPartyType = tpt.TypeID
        GROUP BY tp.id, tpt.[Type], ft.CurrencyCode
    )

    SELECT
        ThirdPartyID,
        ThirdPartyTypeID, 
        CAST(LastTransactionID AS BIGINT) AS LastTransactionID,

        CASE 
            WHEN TotalAmount < 0 THEN CONCAT('(', CAST(ABS(TotalAmount) AS numeric(18,2)), ')')
            ELSE CAST(ABS(TotalAmount) AS numeric(18,2))
        END AS Balances,

        CASE 
            WHEN TotalLocal < 0 THEN CONCAT('(', CAST(ABS(TotalLocal) AS numeric(18,2)), ')')
            ELSE CAST(ABS(TotalLocal) AS numeric(18,2))
        END AS LocalBalances,

        CASE 
            WHEN TotalForeign < 0 THEN CONCAT('(', CAST(ABS(TotalForeign) AS numeric(18,2)), ')')
            ELSE CAST(ABS(TotalForeign) AS numeric(18,2))
        END AS ForeignBalances

    INTO #FinanceCustomerBalances 
    FROM CustomerBalances;

    -- Optional: uncomment if you want to replace type IDs with readable names
    /*
    UPDATE ft 
    SET ft.ThirdPartyTypeID = COALESCE(cd.Description, ft.ThirdPartyTypeID)
    FROM #FinanceCustomerBalances ft
    LEFT JOIN t_CodeDetails cd 
        ON ft.ThirdPartyTypeID = cd.CodeID 
       AND cd.CodeID = 'ThirdPartyType';
    */

    SELECT * 
    FROM #FinanceCustomerBalances
    ORDER BY ThirdPartyID;
    
    DROP TABLE IF EXISTS #FinanceCustomerBalances;

END;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_GetCustomerBalances");
    }
};
