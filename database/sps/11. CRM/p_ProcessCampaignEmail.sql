--created eric.muroki 2025-07-07 | Mureithi Maina 2026-02-18
Create or ALTER PROCEDURE [dbo].[p_ProcessCampaignEmail](
    @CampaignID int,
    @UserID int,
    @ActivityDesc varchar(1000),
    @CampaignRef varchar(255),
    @CampaignSubject varchar(200),
    @CampaignMessage varchar(max),
    @CampaignSource varchar(50)
)
AS
BEGIN
    SET NOCOUNT ON

    BEGIN TRY
    DECLARE @CampaignParty varchar(50) = 'CampaignParty',
        @CampaignName varchar(50) = 'CampaignID'

    IF OBJECT_ID('tempdb..#CampaignList') IS NOT NULL
        DROP TABLE #CampaignList;

    CREATE TABLE #CampaignList
    (
        Email										nVARCHAR(250),
        ContactName nVARCHAR(250),
        partyID     varchar(20),
        ContactID									VARCHAR(16)
    )

    --checking conditions

    IF @CampaignSource = 'ClientID'
        INSERT INTO #CampaignList
        select c.Email,
               c.Name,
               c.ClientID,
               p.Id
        from t_CampaignParties P (NOLOCK)
                 JOIN syn_t_Client C (NOLOCK) ON P.PartyID = C.ClientID
        WHERE CampaignId = @CampaignID
          AND P.Party = @CampaignSource

    IF @CampaignSource = 'LeadID'
        INSERT INTO #CampaignList
        select l.Email,
               l.Name + ' ' + l.OtherNames as Name,
               l.LeadID,
               p.Id
        from t_CampaignParties P (NOLOCK)
                 JOIN t_leads L (NOLOCK) ON P.PartyID = l.LeadID
        WHERE CampaignId = @CampaignID
          AND P.Party = @CampaignSource

    IF @CampaignSource = 'LoanID'
        INSERT INTO #CampaignList
        select c.Email,
               COALESCE(A.AccountName, C.Name),
               A.ClientID,
               p.Id
        from t_CampaignParties P (NOLOCK)
                 JOIN syn_t_AdvancesReport A (NOLOCK) ON P.PartyID = A.AccountID
                 JOIN syn_t_Client C (NOLOCK) ON A.ClientID = C.ClientID
        WHERE CampaignId = @CampaignID
          AND P.Party = @CampaignSource

    INSERT INTO t_Emails (MailID, [To], [Subject], Body, [Text], [Source], SourceID, Party, PartyID, CreatedBy,
                             ModifiedBy, CreatedOn, ModifiedOn, [Status])--, [Channel]
    select @CampaignRef + '-' + b.ContactID + CAST(ABS(CHECKSUM(NEWID())) % 100000 AS VARCHAR),
           CONCAT('[{"', b.ContactName, '":"', b.Email, '"}]') AS JsonResult,
           @CampaignSubject,
           @CampaignMessage,
           @CampaignMessage,
           @CampaignParty,
           b.ContactID,
           @CampaignSource,
           b.partyID,
           @UserID,
           @UserID,
           getdate(),
           getdate(),
           'q'
     --      'ifbp'
    from #CampaignList b (NOLOCK)
    WHERE LEN(b.Email) >= 5

    --set this mureithi.maina 2025-10-03
    UPDATE t
    set t.Status='f'
    from t_CampaignParties t
             left Join #CampaignList b on t.Id = b.ContactID
    WHERE LEN(b.Email) < 5

    UPDATE t
    set t.Status='p',
        t.Channel='SMSId'
    from t_CampaignParties t
             left Join #CampaignList b on t.Id = b.ContactID
    WHERE LEN(b.Email) >= 5

    update c set c.Status = 'p' from t_Campaigns c where c.Id = @CampaignID

    INSERT INTO t_PartyActivities (Party, PartyID, UserID, Notes, ActivityType, ActivityTypeID, CreatedBy, CreatedOn,
                                   ModifiedBy, ModifiedOn)
    select @CampaignSource,
           h.partyID,
           @UserID,
           @ActivityDesc,
           @CampaignName,
           @CampaignID,
           @UserID,
           getdate(),
           @UserID,
           getdate()
    from #CampaignList h (NOLOCK)
    END TRY
    BEGIN CATCH
        DECLARE
            @ErrMsg nvarchar(4000) = ERROR_MESSAGE();
        --@ErrSeverity int = ERROR_SEVERITY(),
        --@ErrState int = ERROR_STATE(),
        --@ErrNumber int = ERROR_NUMBER(),
        --@ErrLine int = ERROR_LINE();

        -- DECLARE @ErrorMessage varchar(2000) = 'p_ProcessCampaignEmail failed. Number: ' + CAST(@ErrNumber AS varchar(10)) + ', Line: ' + CAST(@ErrLine AS varchar(10)) + ', Message: ' + @ErrMsg;

        -- mark campaign and contacts as failed when an error occurs
        BEGIN TRY
            UPDATE t_Campaigns SET Status = 'f', Processing = 0 WHERE Id = @CampaignID;

            UPDATE t_CampaignParties
            SET Status = 'f'
            WHERE CampaignId = @CampaignID
              AND Party = @CampaignSource;
        END TRY
        BEGIN CATCH
            -- swallow secondary errors in failure handling
        END CATCH;

       /* BEGIN TRY
            EXEC dbo.p_systemErrorNotification @ErrorMessage = @ErrMsg;
        END TRY
        BEGIN CATCH
            -- swallow notification errors
        END CATCH;*/
        -- RAISERROR('p_ProcessCampaignEmail failed. Number: %d, Line: %d, Message: %s',
        --    @ErrSeverity, 1, @ErrNumber, @ErrLine, @ErrMsg);
        -- RETURN;
    END CATCH

    SET NOCOUNT ON
END

