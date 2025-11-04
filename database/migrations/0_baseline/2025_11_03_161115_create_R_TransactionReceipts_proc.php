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
        DB::unprepared("CREATE    PROC [dbo].[r_TransactionReceipts] 

		@FromDate DATE = NULL,
        @ToDate DATE = NULL,
        @Status VARCHAR(200)= NULL
AS
BEGIN

    SET NOCOUNT ON;

    CREATE TABLE #TransactionReceipts
    (
        ReceiptId      NVARCHAR(200),
        TransferId     NVARCHAR(200),
        ReceivedBy     VARCHAR(200),
        ReceivedDate   DATE,
        GeneralRemarks VARCHAR(200),
        Status         VARCHAR(200),
  
      CreatedBy      VARCHAR(200),
        CreatedOn      DATE
    );
    INSERT INTO #TransactionReceipts
    (ReceiptId,
     TransferId,
     ReceivedBy,
     ReceivedDate,
     GeneralRemarks,
     Status,
     CreatedBy,
     CreatedOn)
    SELECT TR.ReceiptId,
           TR.TransferId,
           U.Name         AS ReceivedBy,
           TR.ReceivedDate,
           TR.GeneralRemarks,
           CD.Description AS Status,
           U2.Name        AS CreatedBy,
           TR.CreatedOn
    FROM t_TransactionReceipts TR
             JOIN t_Users U ON U.Id = TR.CreatedBy
             JOIN t_Users U2 on U2.Id = TR.CreatedBy
             JOIN t_CodeDetails CD ON CD.Value = TR.Status
    WHERE (@FromDate IS NULL OR TR.ReceivedDate >= @FromDate)
      AND (@ToDate IS NULL OR TR.ReceivedDate < DATEADD(DAY, 1, @ToDate)
        AND (@Status = 'ALL' OR CD.Description = @Status)
        AND CD.CodeID = 'RequisitionStatus');
    SELECT* FROM #TransactionReceipts
    DROP TABLE #TransactionReceipts
END
--GO
EXEC R_TransactionReceipts
--GO
select * from t_Transfers

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_TransactionReceipts");
    }
};
