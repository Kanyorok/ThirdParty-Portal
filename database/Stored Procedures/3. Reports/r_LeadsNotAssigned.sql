CREATE OR ALTER PROCEDURE [dbo].[r_LeadsNotAssigned](
    @FromDate DATETIME =null,
    @ToDate DATETIME =null,
    @Industry VARCHAR(100)=null
)
AS
BEGIN
    SET NOCOUNT ON;

    -- Drop temporary table if it exists
    IF OBJECT_ID('tempdb..#LeadsNotAssigned') IS NOT NULL
        DROP TABLE #LeadsNotAssigned;

    -- Create temporary table
    CREATE TABLE #LeadsNotAssigned
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
    INSERT INTO #LeadsNotAssigned (LeadID,
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

           l.name                                  as LocationID,

           cd.Description                          as Industry,

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
             JOIN t_CodeDetails cd WITH (NOLOCK) ON b.Industry = cd.ID AND cd.CodeID = 'Industries'
             JOIN t_Localities l WITH (NOLOCK) ON b.LocationID = l.ID


    WHERE (u.Name = 'user Default' OR
           u.ID IS NULL) -- unassigned leads

      AND (@Industry IS NULL OR b.Industry = @Industry)
      AND (@FromDate IS NULL OR b.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR b.CreatedOn < DATEADD(DAY, 1, @ToDate));

    -- Resolve LocalityAreaname
    --   UPDATE r
    --   SET r.LocalityArea = l.Name
    --   FROM #LeadsNotAssigned r
    --JOIN t_Localities l WITH (NOLOCK) ON r.LocalityArea = l.ID;

    -- Resolve Industry description
    --UPDATE r
    --SET r.Industry = cd.Description
    --FROM #LeadsNotAssigned r
    --JOIN t_CodeDetails cd WITH (NOLOCK) ON r.Industry = cd.ID AND cd.CodeID = 'Industries';

    -- Output result
    SELECT * FROM #LeadsNotAssigned;

    SET NOCOUNT OFF;
END

--select *from t_CodeDetails
GO
