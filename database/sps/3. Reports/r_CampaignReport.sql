CREATE OR ALTER PROCEDURE [dbo].[r_CampaignReport](
    @CampaignID VARCHAR(1000)=NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #CampaignReport
    (
        CampaignID      NVARCHAR(max),
        CampaignLabel   NVARCHAR(max),
        CampaignStatus  NVARCHAR(100),

        CampaignType    NVARCHAR(100),
        CampaignDetails NVARCHAR(1000),
        CampaignNotes   NVARCHAR(1000),
        --MarketingLabel NVARCHAR(1000),
        --MarketingNotes NVARCHAR(1000),
        CreatedBy       NVARCHAR(100),
        CreatedOn       DATETIME,


        ModifiedBy      NVARCHAR(100),
        ModifiedOn      DATETIME,
        DeletedBy       NVARCHAR(100),
        DeletedOn       DATETIME,
        SMSPhone        nVARCHAR(20),
        SMSStatus       VARCHAR(100),
        SMSClient       VARCHAR(100),
        SMSDate         datetime
    );

    INSERT
    INTO #CampaignReport
    SELECT c.CampaignID,
           c.Label                                                                                            AS CampaignLabel,
           case
               when c.Status = 'd' then 'Draft'
               when c.status = 'a' then 'Approval'
               when c.status = 's' then 'Sent'
               when c.status = 'f' then 'Failed'
               when c.status = 'r' then 'Processing'
               when c.status = 'p'
                   then 'Sending' END                                                                         AS CampaignStatus,
           case
               when c.Type = 's' then 'SMS Campaign'
               when c.Type = 'e' then 'Email Campaign'
               ELSE c.Type END                                                                                AS CampaignType,
           isnull(s.Content, c.Details)                                                                       AS CampaignDetails,
           c.Notes                                                                                            AS CampaignNotes,
           (SELECT f.Name FROM t_users f WHERE f.id = c.CreatedBy)                                            AS CreatedBy,
           c.CreatedOn,
           (SELECT f.Name FROM t_users f WHERE f.id = c.ModifiedBy)                                           AS ModifiedBy,
           c.ModifiedOn,

           (SELECT f.Name FROM t_users f WHERE f.id = c.DeletedBy)                                            AS DeletedBy,
           c.DeletedOn,
           s.Phone                                                                                            AS SMSPhone,
           CASE
               WHEN s.Status = 'p' THEN 'Pending'
               WHEN s.Status = 'f' THEN 'Failed'
               WHEN s.Status = 's' THEN 'Sent'
               ELSE s.Status
               END                                                                                            AS SMSStatus,
           isnull(k.name, s.PartyID),
           s.CreatedOn
    FROM t_Campaigns c
             LEFT JOIN t_CampaignParties cp ON c.Id = cp.CampaignId
             LEFT JOIN t_SMS s ON cp.ChannelID = s.Id
        AND cp.Id = s.SourceID
        AND s.Source = 'CampaignParty'

             LEFT JOIN
         syn_t_Client k on s.Phone = k.Mobile
    WHERE c.CampaignID = @CampaignID;


    SELECT * FROM #CampaignReport (NOLOCK);


    SET NOCOUNT OFF;
END;

--GO
