CREATE OR ALTER PROCEDURE [dbo].[r_LeadsWithAppointment](
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL,
    --  @Industry  VARCHAR(100) = NULL,
    @Meetingstatus VARCHAR(100) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    -- Drop temporary table if it exists
    IF OBJECT_ID('tempdb..#LeadsWithAppointment') IS NOT NULL
        DROP TABLE #LeadsWithAppointment;

    -- Create temporary table
    CREATE TABLE #LeadsWithAppointment
    (
        LeadID              BIGINT,
        LeadPerson          VARCHAR(1000),
        LeadPersonEmail     VARCHAR(1000),
        LeadPersonPhone     VARCHAR(1000),
        LeadPersonType      VARCHAR(500),
        LeadStatus          VARCHAR(100),
        LeadProduct         VARCHAR(200),
        RelationshipManager VARCHAR(100),
        LocalityArea        VARCHAR(200),
        Industry            VARCHAR(200),
        Source              VARCHAR(100),
        CustomerType        VARCHAR(200),
        LeadNotes           VARCHAR(MAX),
        JobTitle            VARCHAR(400),
        LastContacted       DATETIME,
        LeadCreatedBy       VARCHAR(200),
        LeadCreatedOn       DATETIME,
        LeadSource          VARCHAR(100),
        MeetingTitle        VARCHAR(200),
        MeetingStatus       VARCHAR(20),
        MeetingLocation     VARCHAR(200),
        MeetingStartOn      DATETIME,
        MeetingEndOn        DATETIME
    );

    -- Insert data
    INSERT INTO #LeadsWithAppointment (LeadID,
                                       LeadPerson,
                                       LeadPersonEmail,
                                       LeadPersonPhone,
                                       LeadPersonType,
                                       LeadStatus,
                                       LeadProduct,
                                       RelationshipManager,
                                       LocalityArea,
                                       Industry,
                                       Source,
                                       CustomerType,
                                       LeadNotes,
                                       JobTitle,
                                       LastContacted,
                                       LeadCreatedBy,
                                       LeadCreatedOn,
                                       LeadSource,
                                       MeetingTitle,
                                       MeetingStatus,
                                       MeetingLocation,
                                       MeetingStartOn,
                                       MeetingEndOn)
    SELECT b.LeadID,
           b.Name                                  AS LeadPerson,
           b.Email                                 AS LeadPersonEmail,
           b.Phone                                 AS LeadPersonPhone,
           CASE
               WHEN b.Type = 'c' THEN 'Company'
               WHEN b.Type = 'i' THEN 'Individual'
               END                                 AS LeadPersonType,
           ISNULL(v.Description, b.Status)         AS LeadStatus,
           lp.ProductName                          AS LeadProduct,
           ISNULL(u.Name, b.RelationshipManagerID) AS RelationshipManager,
           b.LocationID,
           b.Industry,
           b.Source,
           ISNULL(p.Description, b.CustomerType)   AS CustomerType,
           b.Notes                                 AS LeadNotes,
           b.JobTitle,
           NULL                                    AS LastContacted,
           ISNULL(j.Name, b.CreatedBy)             AS LeadCreatedBy,
           b.CreatedOn,
           b.Source                                AS LeadSource,
           m.Title,
           CASE
               WHEN m.StatusID = 'cc' THEN 'Canceled'
               WHEN m.StatusID = 'sc' THEN 'Scheduled'
               WHEN m.StatusID = 'so' THEN 'Ongoing'
               WHEN m.StatusID = 'ss' THEN 'Completed'
               END                                 AS MeetingStatus,
           m.[Location],
           m.StartOn,
           m.EndOn
    FROM t_Leads b WITH (NOLOCK)
             JOIN t_LeadProducts lp WITH (NOLOCK) ON lp.LeadID = b.LeadID
             JOIN t_ScheduleLeads sl WITH (NOLOCK) ON b.LeadID = sl.LeadId
             LEFT JOIN t_MeetingLeads ml WITH (NOLOCK) ON b.LeadID = ml.LeadId
             LEFT JOIN t_Meetings m WITH (NOLOCK) ON ml.MeetingId = m.MeetingID
             LEFT JOIN t_Users u WITH (NOLOCK) ON b.RelationshipManagerID = u.ID
             LEFT JOIN t_Users j WITH (NOLOCK) ON b.CreatedBy = j.ID
             LEFT JOIN t_CodeDetails v WITH (NOLOCK) ON b.Status = v.Value AND v.CodeID = 'LeadStatus'
             LEFT JOIN t_CodeDetails p WITH (NOLOCK) ON b.CustomerType = p.ID AND p.CodeID = 'CustomerTypes'
             LEFT JOIN t_CodeDetails N WITH (NOLOCK) ON m.StatusID = n.Value AND N.CodeID = 'Meetingstatus'
    WHERE u.Name = 'system'
      AND (@FromDate IS NULL OR b.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR b.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (@Meetingstatus IS NULL OR m.StatusID = @Meetingstatus);

    -- Resolve Locality name
    UPDATE r
    SET r.LocalityArea = l.Name
    FROM #LeadsWithAppointment r
             JOIN t_Localities l WITH (NOLOCK) ON r.LocalityArea = l.ID;

    -- Resolve Industry description
    --UPDATE r
    --SET r.Industry = cd.Description
    --FROM #LeadsWithAppointment r
    --JOIN t_CodeDetails cd WITH (NOLOCK) ON r.Industry = cd.ID AND cd.CodeID = 'Industries';

    -- Output result
    SELECT * FROM #LeadsWithAppointment;

    SET NOCOUNT OFF;
END;



--select * from t_Meetings
--select * from t_CodeDetails
--select * from t_leads
--select * from
GO
