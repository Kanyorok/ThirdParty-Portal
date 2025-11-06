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
        DB::unprepared("--R_BancassuranceClaimClosures '10/10/2024','10/10/2025'

CREATE      PROC [dbo].[R_BancassuranceClaimClosures] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #R_BancassuranceClaimClosures (
  Description Nvarchar(100) ,
  FinalStatus Nvarchar(100),
  --ClosedBy  bigint,
  UserID Nvarchar(100),
 -- PaidBy Nvarchar(100),
 BranchID nvarchar(200),
  CreatedOn DateTime
)
Insert Into #R_BancassuranceClaimClosures
Select

  CD.Description,
  BC.FinalStatus,
  --BC.Closedby,
  US.UserID,
  BR.BranchId,
  BC.CreatedOn
 

from  t_BancassuranceClaimClosures BC  inner join [t_BancassuranceClaims ] BSC ON BSC.ID=BC.ClaimId

Inner join t_CodeDetails CD ON CD.ID = BSC.ClaimType 
Inner join t_users US ON US.ID =BC.CreatedBy
inner join t_Users USR ON USR.Id =BC.ClosedBy
inner join t_branches BR ON BR.UserId = USR.Id
Where BC.createdon between @StartDate and @EndDate

--UPDATE c
--SET c.ClosedBy = CAST(u.UserID AS nvarchar)
--FROM #R_BancassuranceClaimClosures AS c
--JOIN t_Users AS u
--  ON TRY_CAST(u.UserID AS nvarchar) = c.ClosedBy
--WHERE TRY_CAST(u.UserID AS nvarchar) IS NOT NULL;



Update #R_BancassuranceClaimClosures set FinalStatus=(
select Description from t_CodeDetails where #R_BancassuranceClaimClosures.FinalStatus = 
cast(isnull(t_CodeDetails.ID,0 )as nvarchar))

Select * from #R_BancassuranceClaimClosures

Drop Table #R_BancassuranceClaimClosures
END

--Select * from t_Users where codeID like '%created%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BancassuranceClaimClosures");
    }
};
