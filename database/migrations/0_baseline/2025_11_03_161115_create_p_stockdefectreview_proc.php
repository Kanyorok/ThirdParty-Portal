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
        DB::unprepared("Create   procedure p_stockdefectreview

			@FromDate datetime = null,
                @ToDate datetime = null,
                @BranchID Varchar(100)=null
AS
BEGIN
    Create table #stockdefectreview
    (
        ItemName            Varchar(100),
        FromBranch      Varchar(100),
        --Store           Varchar(100),
        Quantity        Int,
        Defect          Varchar(200),
        Condition
          Varchar(100),
        Status          Varchar(100),
        CreatedBy       Varchar(100),
        CreatedOn       Date

    )

    Insert into #stockdefectreview

select 
	I.ItemName as ItemName,
	B.Name as FromBranch,
	--S.StoreName as store,
	Quantity,
	CO.Description as Defect,
	C.Description as condition,
	CD.Description as [Status],
	U.Name as CreatedBy,
	D.CreatedOn

from t_Defects D
	join t_Items I on D.ItemID = I.Id
	join t_Branches B on D.FromBranch = B.Id
	--join t_Stores S on D.Store = S.Id
	join t_CodeDetails CO on D.Defect = CO.ID 
	join t_CodeDetails C on D.Condition = C.ID 
	join t_CodeDetails CD on D.Status = CD.Value 
	Join t_users U on D.CreatedBy = U.Id

	  where (@FromDate IS NULL OR I.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR I.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (@BranchID IS NULL OR @BranchID = 'ALL' OR B.Name IN (SELECT value FROM STRING_SPLIT(@BranchID, ',')));

    select *
 from #stockdefectreview
end
--EXEC p_stockdefectreview


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_stockdefectreview");
    }
};
