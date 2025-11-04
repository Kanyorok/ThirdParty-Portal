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
        DB::unprepared("Create   procedure [dbo].[r_legalobligations]
as
begin
    create table #legalobligations
    (
        Obligation   Varchar(200),
        Source       Varchar(100),
        DueDate      Date,
        Status       Varchar(100),
        Descriptions Varchar(255),
        Active       Varchar(100),
        Assignedto   Varchar(100),
        ScheduleID   Varchar(100),
        CreatedDate  Date
    )
    insert into #legalobligations
    select O.Title,
           O.SourceType,
           O.DueDate,
           O.Status,
           O.Description,
           CASE
               WHEN O.IsActive = '1' THEN 'Yes'
               WHEN O.IsActive = '0' THEN 'No'
               ELSE CAST(O.IsActive AS VARCHAR)
               END AS IsActive,
           U.Name  as [AssignedTo],
           O.ScheduledID,
           O.CreatedOn

    From t_LegalObligations O
             Join t_users U on U.ID = O.AssignedTo

    select * from #legalobligations
END
--GO
--EXEC r_legalobligations


--select *  From t_LegalObligations
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_legalobligations");
    }
};
