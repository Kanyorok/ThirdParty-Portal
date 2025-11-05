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
        DB::unprepared("-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
--R_BancassurancePolicyRenewals '10/10/2024','10/10/2025'
CREATE   PROCEDURE [dbo].[R_BancassurancePolicyRenewals]  
	-- Add the parameters for the stored procedure here
	@StartDate DateTime,
	@EndDate DateTime
	--@Status nvarchar
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

     Create Table #BancassurancePolicyRenewals(

	 PolicyNumber Nvarchar (100),
	 RenewalDate DateTime,
	 NewStartDate DateTime,
	 NewEndDate DateTime,
	 BranchID Nvarchar (100),
	 UserID Nvarchar(100),
	 CreatedOn DateTime 

	 )
	 Insert into #BancassurancePolicyRenewals
	 Select 
	 BP.PolicyNumber,
	 RenewalDate,
	 NewStartDate,
	 NewEndDate,
	 B.BranchID,
	 US.UserID,
	 BAR.CreatedOn


	  From t_BancassurancePolicyRenewals BAR  Inner join t_BancassurancePolicies BP ON BP.ID =BAR.PolicyID
	  Inner Join t_Users US ON US.ID =BAR.CreatedBy
	 -- Inner join t_UserBranch UB ON UB.UserId = US.UserID
	  Inner join t_Branches B ON B.UserId =US.ID
	  
	  where BAR.CreatedOn between @StartDate and @EndDate --and 
	    --BAR.Status=@Status

		Select * from #BancassurancePolicyRenewals

		Drop table #BancassurancePolicyRenewals
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BancassurancePolicyRenewals");
    }
};
