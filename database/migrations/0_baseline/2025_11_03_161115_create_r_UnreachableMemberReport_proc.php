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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_UnreachableMemberReport](
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT COALESCE(cl.Name, cc.CompanyName, co.Label) AS Receiver,
           c.StartOn,

           c.EndOn,
           cd.Description                              AS CallStatus,
           u.Name                                      AS Caller,
           c.CreatedOn                                 AS CallDate,
           COALESCE(cl.mobile, co.Phone)               as MobileNo
    FROM t_Calls c
             LEFT JOIN syn_t_Client cl
                       ON c.PartyID = cl.ClientID AND c.Party = 'ClientID'
             LEFT JOIN syn_t_ClientCorporate cc
                       ON c.PartyID = cc.ClientID AND c.Party = 'ClientID'
             LEFT JOIN t_Contacts co
                       ON c.PartyID = co.ContactID AND c.Party = 'ContactID'
             LEFT JOIN t_CodeDetails cd
                       ON c.CallStatusID = cd.ID AND cd.CodeID = 'CallStatusID'
             LEFT JOIN t_Users u
                       ON c.UserID = u.UserID
    WHERE (@FromDate IS NULL OR c.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR c.CreatedOn < DATEADD(DAY, 1, @ToDate))
    ORDER BY c.CreatedOn DESC;

    SET NOCOUNT OFF;
END;
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_UnreachableMemberReport");
    }
};
