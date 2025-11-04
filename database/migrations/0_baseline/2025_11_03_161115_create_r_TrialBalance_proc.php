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
        DB::unprepared("CREATE   PROCEDURE dbo.r_TrialBalance
(
	@BranchID           VARCHAR(200)='',
	@FromDate			DATETIME=NULL,
	@ToDate				DATETIME=NULL
	
)  

AS
BEGIN
	SET NOCOUNT ON
CREATE TABLE #TB
	(
	Branch				NVARCHAR(200) NULL,
	AccountID			NVARCHAR(200),
	GLName				NVARCHAR(200),
	GLAccountType		NVARCHAR(200),
	OpeningBalance		MONEY,
	ClosingBalance		MONEY,

 )
		

Begin
INSERT INTO #TB

			SELECT  
				CAST(B.BranchID AS VARCHAR) + ' - ' + B.Name AS [BranchID],
				GL.GLCode as [Account ID],
				FG.GLName,
				CASE GL.GLAccountType
				WHEN 'A' THEN 'Asset'
				WHEN 'I' THEN 'Income'
				WHEN 'E' THEN 'Expense'
				WHEN 'L' THEN 'Liability'
				WHEN 'S' THEN 'Shares'
				Else 'Unknown'
				END AS GLAccountType,
				CB.OpeningBalance,
				CB.ClosingBalance

		FROM t_GLClosingBalances CB join t_FinanceGLAccounts FA on CB.GLAccountID=FA.id	
		JOIN t_financeGLBranch GL  ON GL.GLAccountID=CB.GLAccountID
		JOIN t_FinanceGLAccounts FG ON FG.ID =GL.GLAccountID
		JOIN t_branches B ON B.ID =GL.BranchID
	

	WHERE 

	@FromDate IS NULL 

	OR(GL.CreatedOn>=@Fromdate

	AND GL.CreatedOn<DATEADD(DAY,1,@Todate)

	)
	AND (
	@branchID IS NULL OR
	@branchID = 'ALL' OR 
	B.Name IN (SELECT value FROM string_split(@branchID,','))
	)

	END

	select * from #TB
 END
	

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_TrialBalance");
    }
};
