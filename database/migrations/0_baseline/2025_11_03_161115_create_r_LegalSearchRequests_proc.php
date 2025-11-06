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
        DB::unprepared("create   procedure [dbo].[r_LegalSearchRequests]
as
begin
    create table #LegalSearchRequests
    (
        RequestType    Varchar(200),
        EntityName     Varchar(200),
        RequestedBy    Varchar(100),
        Status         Varchar(100),
        ApprovalReason Varchar(200),
        Remarks        Varchar(255),
        RequestDate    Date,
        CreatedDate    Date
    )
    insert into #LegalSearchRequests
    select L.RequestType,
           L.EntityName,
           U.Name as [RequestedBy],
           L.Status,
           L.ApprovalReason,
           L.Remarks,
           L.RequestDate,
           L.CreatedOn

    from t_LegalSearchRequests L
             Join t_users U ON U.ID = L.RequestedBy
    select * from #LegalSearchRequests
END
--GO
--EXEC r_LegalSearchRequests


--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalSearchRequests");
    }
};
