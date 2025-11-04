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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_TransactionApproval](
    @FromDate DATE = NULL,
    @ToDate DATE = NULL
)
AS
BEGIN
    SET NOCOUNT ON

    CREATE TABLE #TransactionApproval
    (
        ID           VARCHAR(200),
        REFNO        VARCHAR(200),
        FromBranch   VARCHAR(200),
        ToBranch     VARCHAR(200),
        TransferDate DATE,
        InitiatedBy  VARCHAR(200),
        Status       VARCHAR(200),
        ModifiedBy   VARCHAR(200),
        ModifiedOn   DATE
    )

    INSERT INTO #TransactionApproval
    (ID,
     REFNO,
     FromBranch,
     ToBranch,
     TransferDate,
     InitiatedBy,
     Status,
     ModifiedBy,
     ModifiedOn)
    SELECT R.ID,
           R.TransferID  AS REFNO,
           B1.Name       AS FromBranch,
           B2.Name       AS ToBranch,
           R.TransferDate,
           US.Name       as InitiatedBy,
           C.Description AS Status,
           U.Name        AS ModifiedBy,
           R.ModifiedOn
    FROM t_transfers AS R
             JOIN t_Branches AS B1 ON B1.ID = R.FromBranch
             JOIN t_Branches AS B2 ON B2.ID = R.ToBranch
             JOIN t_CodeDetails AS C ON C.Value = R.Status
        --AND C.CodeID='RequistionStatus'
             JOIN t_users AS US ON US.ID = R.Id
             JOIN t_users AS U ON U.ID = R.ModifiedBy
    WHERE (@FromDate IS NULL OR R.TransferDate >= @FromDate)
      AND (@ToDate IS NULL OR R.TransferDate < DATEADD(DAY, 1, @ToDate))

    ORDER BY R.ID

    SELECT * FROM #TransactionApproval

    DROP TABLE #TransactionApproval

    SET NOCOUNT OFF
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_TransactionApproval");
    }
};
