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
        DB::unprepared("Create     Procedure [dbo].[r_GLStatement2] 
	@FromDate Date =null,
    @ToDate Date = null,
    @BranchID nvarchar(200) =null
AS
BEGIN
    CREATE TABLE #r_GLStatement2
(
        BranchID		nvarchar(200),
		AccountID		nvarchar(200),
		Balance			money,
		TrxDate         Date,
        Debit           Money,
        Credit          Money,
        ClosingBalance  Money
 
    )
 
    Insert into #r_GLStatement2
    Select
	CAST(B.BranchID AS VARCHAR) + ' - ' + B.Name AS [BranchID],
	GL.GLCode,
	Balance,
	GL.CreatedOn,
	J.Debit as GLAccountID,
	J.Credit as GLAccountID,
	Localbalance
 
	from t_FinanceGLBranch GL
 
	
   JOIN t_branches B ON B.ID = GL.BranchID
   JOIN t_financejournallines J ON J.ID=GL.GLAccountID
 
    Where (@FromDate IS NULL OR GL.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR GL.CreatedOn < DATEADD(DAY, 1, @ToDate))
 
      AND (
        @BranchID IS NULL OR
        @BranchID = 'ALL' OR
        B.Name IN (SELECT value FROM STRING_SPLIT(@BranchID, ','))
        );
 
 
    select * from #r_GLStatement2
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_GLStatement2");
    }
};
