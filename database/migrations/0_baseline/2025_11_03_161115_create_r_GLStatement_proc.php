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
        DB::unprepared("Create     Procedure [dbo].[r_GLStatement]

	 @FromDate Date =null,
     @ToDate Date = null,
     @BranchID nvarchar(200) =null
AS
BEGIN
    CREATE TABLE #r_GLStatement
    
(
        GLAccount      nvarchar(200),
        OurBranchID    nvarchar(200),
        TrxDate        Date,
        Debit          Money,
        Credit         Money,
        RunningBalance Money

    )

    Insert into #r_GLStatement
    Select 
		   JL.JournalEntryId,
           B.Name as [BranchID],
           JL.CreatedOn,
           JL.Debit,
           JL.Credit,
           C.LocalBalance as Amount
    from t_FinanceJournalLines JL
             JOIN t_branches B ON B.ID = JL.BranchID
			 JOIN t_GLClosingBalances C ON C.ID=JL.GLAccountID

    Where (@FromDate IS NULL OR JL.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR JL.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @BranchID IS NULL OR
        @BranchID = 'ALL' OR
        B.Name IN (
SELECT value FROM STRING_SPLIT(@BranchID, ','))
        );


    select * from #r_GLStatement
END
--go 
--exec r_GLStatement
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_GLStatement");
    }
};
