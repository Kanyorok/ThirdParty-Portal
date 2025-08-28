CREATE OR ALTER PROCEDURE [dbo].[r_LeadsGroupByRM](
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL,
    --@Industry  VARCHAR(MAX) = NULL,
    @RelationshipManager VARCHAR(MAX)=NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    -- Drop temporary table if it exists
    IF OBJECT_ID('tempdb..#LeadsGroupByRM') IS NOT NULL
        DROP TABLE #LeadsGroupByRM;

    -- Create temporary table
    CREATE TABLE #LeadsGroupByRM
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
        LeadSource          VARCHAR(100)
    );

    -- Insert data
    INSERT INTO #LeadsGroupByRM (LeadID,
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
                                 LeadSource)
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
           b.Source                                AS LeadSource
    FROM t_Leads b WITH (NOLOCK)
             JOIN t_LeadProducts lp WITH (NOLOCK) ON lp.LeadID = b.LeadID
             LEFT JOIN t_Users u WITH (NOLOCK) ON b.RelationshipManagerID = u.ID
             LEFT JOIN t_Users j WITH (NOLOCK) ON b.CreatedBy = j.ID
             LEFT JOIN t_CodeDetails v WITH (NOLOCK) ON b.Status = v.Value AND v.CodeID = 'LeadStatus'
             LEFT JOIN t_CodeDetails p WITH (NOLOCK) ON b.CustomerType = p.ID AND p.CodeID = 'CustomerTypes'
    WHERE @RelationshipManager IS NULL
       OR EXISTS (SELECT 1
                  FROM STRING_SPLIT(@RelationshipManager, ',') s
                  WHERE s.value = u.Name)
        AND (@FromDate IS NULL OR b.CreatedOn >= @FromDate)
        AND (@ToDate IS NULL OR b.CreatedOn < DATEADD(DAY, 1, @ToDate));

    -- Resolve LocalityArea name
    UPDATE r
    SET r.LocalityArea = l.Name
    FROM #LeadsGroupByRM r
             JOIN t_Localities l WITH (NOLOCK) ON CAST(r.LocalityArea AS BIGINT) = l.ID;

    -- Resolve RelationshipManager description
    UPDATE r
    SET r.RelationshipManager =u.Name
    FROM #LeadsGroupByRM r
             JOIN t_Leads b WITH (NOLOCK) ON TRY_CAST(r.RelationshipManager AS BIGINT) = b.RelationshipManagerID
             JOIN t_Users u WITH (NOLOCK) ON b.RelationshipManagerID = u.Id

    -- Output result
    SELECT * FROM #LeadsGroupByRM;

    SET NOCOUNT OFF;
END;
--GO
