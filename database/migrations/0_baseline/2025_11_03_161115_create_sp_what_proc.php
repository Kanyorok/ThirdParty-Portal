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
        DB::unprepared("CREATE procedure [dbo].[sp_what] ( @session int = null, @plan bit = 0 ) 
as 
begin
    select session_id ,command , object_name( s.objectid) as obj,
    SUBSTRING(s.text, statement_start_offset / 2, ( (CASE WHEN statement_end_offset = -1 THEN (LEN(CONVERT(nvarchar(max),s.text)) * 2) ELSE statement_end_offset END) - statement_start_offset) / 2) as CommandText,
    blocking_session_id as blocked, wait_time, wait_resource,
    open_transaction_count as trans , percent_complete as [%], 
    cpu_time , 
    convert(varchar, total_elapsed_time /60000 ) + ':' + right('0' + convert(varchar(2), (total_elapsed_time /1000) % 60 ),2 ) as StartedSince,
    reads , 
    writes ,logical_reads, row_count, nest_level, granted_query_memory as mem 
     
    from sys.dm_Exec_requests r cross apply sys.dm_exec_sql_text (sql_handle) s
    where command is not null and session_id <> @@spid
    and ( session_id = @session or @session is null )
    if ( @plan = 1 )
    begin
        select session_id , query_plan from sys.dm_Exec_requests r
        cross apply sys.dm_exec_query_plan (plan_handle) s
        where command is not null and session_id <> @@spid
        and ( session_id = @session or @session is null )
 
    end
end 





");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_what");
    }
};
