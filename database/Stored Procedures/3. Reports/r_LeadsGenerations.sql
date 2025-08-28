CREATE OR ALTER PROCEDURE [dbo].[r_LeadsGenerations](
    @Industry BIGINT = NULL,
    --@RM           NVARCHAR(MAX) = NULL,
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL,
    @Locality VARCHAR(100) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    -- Drop temporary table if it exists
    IF OBJECT_ID('tempdb..#LeadsGenerations') IS NOT NULL
        DROP TABLE #LeadsGenerations;

    -- Create temporary table to hold leads data
    CREATE TABLE #LeadsGenerations
    (
        LeadID
                        BIGINT,
        LeadPerson      VARCHAR(1000),
        LeadPersonPhone NVARCHAR(50),
        LocalityArea    VARCHAR(100),
        Designation     VARCHAR(100),
        Employer        VARCHAR(100),
        Referee         VARCHAR(100)
    );


    -- Insert data
    INSERT INTO #LeadsGenerations (LeadID,
                                   LeadPerson,
                                   LeadPersonPhone,
                                   LocalityArea,
                                   Designation,
                                   Employer,
                                   Referee)
    SELECT b.LeadID,
           b.Name         AS LeadPerson,

           b.Phone        AS LeadPersonPhone,
           l.Name         AS LocalityArea,
           s.TitleID      AS Designation,
           s.EmployerName AS Employer,
           u.UserID       AS Referee
    FROM t_Leads b WITH (NOLOCK)
             LEFT JOIN t_localities l WITH (NOLOCK) ON l.ID = b.LocationID
             LEFT JOIN t_Users u WITH (NOLOCK) ON b.RelationshipManagerID = u.ID OR b.CreatedBy = u.ID
             LEFT JOIN syn_t_ClientIndividual s WITH (NOLOCK) ON u.ClientID = s.ClientID
    WHERE (@Industry IS NULL OR b.Industry = @Industry)

      AND (@Locality IS NULL OR l.ID = @Locality)
      AND (@FromDate IS NULL OR b.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR b.CreatedOn < DATEADD(DAY, 1, @ToDate))
    --AND (@RM IS NULL OR u.UserID = @RM);

    -- Output result
    SELECT LeadID,
           LeadPerson,
           LeadPersonPhone,
           LocalityArea,
           Designation,
           Employer,
           Referee
    FROM #LeadsGenerations
    ORDER BY LeadID;

    -- Clean up
    DROP TABLE #LeadsGenerations;

    SET NOCOUNT OFF;
END;


--select * from t_Leads

GO
