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
        DB::unprepared("
CREATE    PROCEDURE [dbo].[R_BoardMeeting] 
(    

 @FromDate smalldatetime,
 @ToDate   smalldatetime
)     
AS    
BEGIN    
 SET NOCOUNT ON    
  
 CREATE TABLE #BoardMeeting    
 (   
  MeetingTitle     NVARCHAR(1000),     
  Attendees     NVARCHAR(1000), 
  CommunicationofPrepared   Varchar(20),
  PreparationMinutes   Varchar(300),
  Dateofcommunication   smalldatetime,
  Vanue      Varchar(100),
  Dateofmeeting      Smalldatetime,

 )    
     
 

 INSERT INTO #BoardMeeting    
 SELECT 
 Title,Notes,'','',CreatedOn,'',StartOn
 FROM t_Meetings  (NOLOCK)  
 Where StartOn Between @FromDate and @ToDate
 
    
 SELECT * FROM #BoardMeeting   

    
 SET NOCOUNT OFF     
END    


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BoardMeeting");
    }
};
