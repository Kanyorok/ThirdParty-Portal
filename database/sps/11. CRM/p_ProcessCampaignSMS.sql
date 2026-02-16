--created eric.muriki/Mureithi.Maina 2025-07-10
CREATE OR ALTER PROCEDURE [dbo].[p_ProcessCampaignSMS](
    @CampaignID int,
    @UserID int,
    @ActivityDesc varchar(1000),
    @CampaignRef varchar(255),
    @CampaignMessage varchar(1000),
    @CampaignSource varchar(50)
)
AS
BEGIN
    SET NOCOUNT ON

    DECLARE @CampaignParty varchar(50) = 'CampaignParty',
        @CampaignName varchar(50) = 'CampaignID',
        @TransactionCount INT = @@TRANCOUNT

    BEGIN TRY
        IF @TransactionCount = 0
            BEGIN TRANSACTION;

        IF OBJECT_ID('tempdb..#CampaignList') IS NOT NULL
            DROP TABLE #CampaignList;

        CREATE TABLE #CampaignList
        (
            Mobile           nVARCHAR(30),
            ContactName      nVARCHAR(250),
            SourceID         varchar(20),
            ContactID        VARCHAR(16),
            NormalizedMobile NVARCHAR(30),
            IsValid          BIT DEFAULT 0
        )

        --checking conditions
        IF @CampaignSource = 'ClientID'
            INSERT INTO #CampaignList
            select COALESCE(c.Mobile, c.Phone1, c.Phone2) as Mobile,
                   c.Name,
                   c.ClientID,
                   p.Id,
                   NULL,
                   0
            from t_CampaignParties P (NOLOCK)
                     JOIN syn_t_Client C (NOLOCK) ON P.PartyID = C.ClientID
            WHERE CampaignId = @CampaignID
              AND P.Party = @CampaignSource

        IF @CampaignSource = 'LeadID'
            INSERT INTO #CampaignList
            select l.Phone,
                   l.Name + ' ' + l.OtherNames as Name,
                   l.LeadID,
                   p.Id,
                   NULL,
                   0
            from t_CampaignParties P (NOLOCK)
                     JOIN t_leads L (NOLOCK) ON P.PartyID = l.LeadID
            WHERE CampaignId = @CampaignID
              AND P.Party = @CampaignSource

        IF @CampaignSource = 'LoanID'
            INSERT INTO #CampaignList
            select COALESCE(c.Mobile, c.Phone1, c.Phone2) as Mobile,
                   COALESCE(A.AccountName, C.Name),
                   A.ClientID,
                   p.Id,
                   NULL,
                   0
            from t_CampaignParties P (NOLOCK)
                     LEFT JOIN syn_t_AdvancesReport A (NOLOCK) ON P.PartyID = A.AccountID
                     LEFT JOIN syn_t_Client C (NOLOCK) ON A.ClientID = C.ClientID
            WHERE P.CampaignId = @CampaignID
              AND P.Party = @CampaignSource

            -- Keep only digits from Mobile
            ;
        WITH Cleaned AS (SELECT ContactID,
                                Mobile,
                                (SELECT STRING_AGG(ch, '')
                                 FROM (SELECT SUBSTRING(Mobile, v.number, 1) AS ch
                                       FROM master..spt_values v (NOLOCK)
                                       WHERE v.type = 'P'
                                         AND v.number BETWEEN 1 AND LEN(Mobile)) s
                                 WHERE ch LIKE '[0-9]') AS DigitsOnly
                         FROM #CampaignList)
        UPDATE c
        SET Mobile = COALESCE(x.DigitsOnly, '')
        FROM #CampaignList c (NOLOCK)
                 JOIN Cleaned x ON x.ContactID = c.ContactID

        -- Normalize and validate to 2547XXXXXXXX or 2541XXXXXXXX (no +)
        UPDATE b
        SET NormalizedMobile =
                CASE
                    WHEN LEN(Mobile) > 13 THEN NULL
                    WHEN LEN(Mobile) = 12 AND LEFT(Mobile, 4) IN ('2547', '2541') THEN Mobile
                    WHEN LEN(Mobile) = 10 AND LEFT(Mobile, 2) = '07' THEN '254' + RIGHT(Mobile, 9)
                    WHEN LEN(Mobile) = 10 AND LEFT(Mobile, 2) = '01' THEN '254' + RIGHT(Mobile, 9)
                    WHEN LEN(Mobile) = 9 AND LEFT(Mobile, 1) IN ('7', '1') THEN '254' + Mobile
                    ELSE NULL
                    END,
            IsValid          =
                CASE
                    WHEN LEN(Mobile) > 13 THEN 0
                    WHEN LEN(Mobile) = 12 AND LEFT(Mobile, 4) IN ('2547', '2541') THEN 1
                    WHEN LEN(Mobile) = 10 AND LEFT(Mobile, 2) IN ('07', '01') THEN 1
                    WHEN LEN(Mobile) = 9 AND LEFT(Mobile, 1) IN ('7', '1') THEN 1
                    ELSE 0
                    END
        FROM #CampaignList b (NOLOCK)

        INSERT INTO t_sms (SMSId, Phone, Content, [Source], SourceId, Party, PartyID, CreatedBy,
                           CreatedOn,
                           ModifiedBy, ModifiedON)
        select @CampaignRef + '' + b.ContactID + CAST(ABS(CHECKSUM(NEWID())) % 100000 AS VARCHAR),
               b.NormalizedMobile,
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
        WHERE b.IsValid = 1

        -- mark invalid phone numbers as failed
        UPDATE t
        set t.Status='f'
        from t_CampaignParties t (NOLOCK)
                 left Join #CampaignList b (NOLOCK) on t.Id = b.ContactID
        WHERE b.IsValid = 0
           OR b.NormalizedMobile IS NULL

        UPDATE t
        set t.Status='p',
            t.Channel='SMSId'
        from t_CampaignParties t (NOLOCK)
                 left Join #CampaignList b (NOLOCK) on t.Id = b.ContactID
        WHERE b.IsValid = 1

        update c set c.Status = 'p' from t_Campaigns c (NOLOCK) where c.Id = @CampaignID

        INSERT INTO t_PartyActivities (Party, PartyID, UserID, Notes, ActivityType, ActivityTypeID, CreatedBy,
                                       CreatedOn, ModifiedBy, ModifiedOn)
        select @CampaignSource,
               h.SourceID,
               @UserID,
               @ActivityDesc,
               @CampaignName,
               @CampaignID,
               @UserID,
               getdate(),
               @UserID,
               getdate()
        from #CampaignList h (NOLOCK)
        WHERE h.IsValid = 1

        IF @TransactionCount = 0
            COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        IF @TransactionCount = 0 AND @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();

        -- BEGIN TRY
        --     EXEC dbo.p_systemErrorNotification @ErrorMessage = @ErrorMessage;
        -- END TRY
        -- BEGIN CATCH
        -- swallow notification errors
        -- END CATCH;
    END CATCH

    SET NOCOUNT ON
END
