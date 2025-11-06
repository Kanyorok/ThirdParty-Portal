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
        DB::unprepared("Create     procedure r_SupervisorReport

(
@Assigned  Varchar(100),
@StartDate DateTime,
@EndDate Datetime
)
As
Begin
SET NOCOUNT ON

Create table #SupervisorReport
(
AccountId Nvarchar (max),
Assigned Nvarchar (max),
StartOn  DateTime,
EndOn DateTIme,
Status  Varchar(100),
Createdby varchar(100),
CreatedOn Datetime,
ModifiedBy Varchar(100),
ModifiedON  DateTime,
AccountName Varchar(255) ,
ProductName Nvarchar(max)
)


Insert INTO #SupervisorReport
(
AccountId ,
Assigned ,
StartOn ,
EndOn ,
Status  ,
AccountName,
ProductName,
Createdby,
CreatedOn ,
ModifiedBy ,
ModifiedON  
)
 
SELECT 
    l.AccountId,
    u.Name AS Assigned,
    l.StartOn,
    l.EndOn,
    l.Notes AS Status,
    a.AccountName,
    a.ProductName,
    uc.Name AS CreatedBy,       -- join for CreatedBy
    l.CreatedOn,
    um.Name AS ModifiedBy,      -- join for ModifiedBy
    l.ModifiedOn
FROM t_LoanAssignments l
INNER JOIN t_Users u 
    ON l.UserId = u.Id
LEFT JOIN syn_t_AdvancesReport a 
    ON l.AccountId = a.AccountId
LEFT JOIN t_Users uc 
    ON l.CreatedBy = uc.Id       -- join for CreatedBy name
LEFT JOIN t_Users um 
    ON l.ModifiedBy = um.Id      -- join for ModifiedBy name

where 
 u.Name=@Assigned
--and l.StartOn > @StartDate
AND (@StartDate IS NULL OR l.StartOn >= @StartDate)

-- l.EndOn=@EndDate

select * from t_users

select * from #SupervisorReport


    SET NOCOUNT ON
END


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_SupervisorReport");
    }
};
