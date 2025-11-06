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
CREATE       procedure   [dbo].[r_GuarantorNotifications] 
(

@FromDate  DateTime =NUll,
@ToDate  DateTime =Null,
@SourceID  Nvarchar =NUll 
)
AS
Begin 
Set NOCount ON ;

CREATE  TABLE #Guarantornotifications(

ClientID INT,
GuarantorName  VARCHAR (1000),
PhoneNumber NVARCHAR(1000) ,
Message   VARCHAR (1000),
AccountNumber NVARCHAR (1000),
SmsStatus  VARCHAR (100),
MessageSentOn DateTime  
)

Insert into #Guarantornotifications (

ClientID,
GuarantorName,
PhoneNumber,
Message,
AccountNumber,
SmsStatus,
MessageSentOn
)


SELECT
    e.ClientId,
    e.name,
    f.Phone,
    f.Content,
    f.SourceID,
    f.Status,
    f.Dated
FROM syn_t_AdvancesReport a
JOIN syn_t_AccountCustomer b ON a.AccountID = b.AccountId
JOIN syn_t_AccountGuarantor d ON a.accountid = d.accountid
JOIN syn_t_Client e ON d.guarantorid = e.clientid
JOIN t_sms f ON e.clientid = f.PartyID AND f.source = 'LoanId' AND d.accountid = f.sourceid
And f.Dated>=@FromDate
And f.Dated < DateAdd(Day,1,@ToDate)
Order BY f.Dated Desc;

select * from #Guarantornotifications
set NOcount Off

END;
 

 ---select top 20 * from syn_t_AdvancesReport      

 Select * from t_SMS");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_GuarantorNotifications");
    }
};
