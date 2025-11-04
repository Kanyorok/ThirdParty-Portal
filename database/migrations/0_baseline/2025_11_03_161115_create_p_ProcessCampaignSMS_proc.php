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
        DB::unprepared("--created eric.muriki 2025-07-10
CREATE     PROCEDURE [dbo].[p_ProcessCampaignSMS]
     (
	@CampaignID				int,
	@UserID					int,
	@ActivityDesc			varchar(1000),
	@CampaignRef			varchar(255),
	@CampaignMessage		varchar(1000),
	@CampaignSource			varchar(50)
)
AS
BEGIN
    SET NOCOUNT ON

	DECLARE @CampaignParty varchar(50) = 'CampaignParty',
	@CampaignName varchar(50) = 'CampaignID'

    IF OBJECT_ID('tempdb..#CampaignList') IS NOT NULL
		DROP TABLE #CampaignList;

    CREATE TABLE #CampaignList
    (
        Mobile										nVARCHAR(30),
		ContactName                                  nVARCHAR(250),
		SourceID                                    varchar(20),
        ContactID									VARCHAR(16)
    )

	--checking conditions

	IF @CampaignSource = 'ClientID'
		INSERT INTO #CampaignList
				select COALESCE(c.Mobile,c.Phone1,c.Phone2) as  Mobile,
				c.Name, c.ClientID,p.Id
				from t_CampaignParties P  (NOLOCK)
				JOIN syn_t_Client C  (NOLOCK)  ON P.PartyID=C.ClientID
				WHERE CampaignId = @CampaignID AND P.Party = @CampaignSource

	IF @CampaignSource = 'LeadID'
		INSERT INTO #CampaignList
				select l.Phone,
				l.Name + ' '+ l.OtherNames as Name, l.LeadID,p.Id
				from t_CampaignParties P  (NOLOCK)
				JOIN t_leads L  (NOLOCK)  ON P.PartyID=l.LeadID
				WHERE CampaignId = @CampaignID AND P.Party = @CampaignSource

    IF @CampaignSource = 'LoanID'
		INSERT INTO #CampaignList
				select COALESCE(c.Mobile,c.Phone1,c.Phone2) as  Mobile,
				COALESCE(A.AccountName,C.Name), A.ClientID,p.Id
				from t_CampaignParties P  (NOLOCK)
				LEFT JOIN syn_t_AdvancesReport A  (NOLOCK)  ON P.PartyID=A.AccountID
				LEFT JOIN syn_t_Client C (NOLOCK) ON A.ClientID=C.ClientID
				WHERE P.CampaignId = @CampaignID AND P.Party = @CampaignSource


    INSERT INTO t_sms (SMSId, Phone, Content, [Source], SourceId, Party, PartyID, CreatedBy, CreatedOn,
                       ModifiedBy, ModifiedON)
	select
	@CampaignRef + '' +b.ContactID + CAST(ABS(CHECKSUM(NEWID())) % 100000 AS VARCHAR),
	b.Mobile,
	REPLACE(@CampaignMessage, '#name', b.ContactName),
	@CampaignParty,
	b.ContactID,
	@CampaignSource,
	b.SourceID,
	@UserID,
	getdate(),
	@UserID,
	getdate()
	from #CampaignList b (NOLOCK)
	WHERE	LEN(b.Mobile)>=9

    --remove all min phone numbers --mureithi.maina 2025-10-01
	UPDATE t set t.Status='f' from t_CampaignParties t
	left Join #CampaignList b on t.Id = b.ContactID
	WHERE	LEN(b.Mobile)<9

	UPDATE t set t.Status='p', t.Channel='SMSId' from t_CampaignParties t
	left Join #CampaignList b on t.Id = b.ContactID
	WHERE	LEN(b.Mobile)>=9

    update c set c.Status = 'p' from t_Campaigns c where c.Id = @CampaignID

	INSERT INTO t_PartyActivities (Party,PartyID,UserID,Notes,ActivityType,ActivityTypeID,CreatedBy,CreatedOn,ModifiedBy,ModifiedOn)
	select
	@CampaignSource,
	h.SourceID,
	@UserID,
	@ActivityDesc,
	@CampaignName,
	@CampaignID,
	@UserID,
	getdate(),
	@UserID,
	getdate()
	from #CampaignList h  (NOLOCK) WHERE	LEN(h.Mobile)>=9

    SET NOCOUNT ON
END



---select * from t_SMS");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_ProcessCampaignSMS");
    }
};
