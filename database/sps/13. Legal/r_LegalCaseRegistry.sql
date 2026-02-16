CREATE OR ALTER PROCEDURE [dbo].[r_LegalCaseRegistry]
    @StatusFilter NVARCHAR(50) = NULL  -- Optional: 'Open' or 'Closed'
AS
BEGIN
    SET NOCOUNT ON;

    -- Temporary table to hold results
    CREATE TABLE #r_LegalCaseRegistry
    (
        CaseTitle NVARCHAR(200),
        CaseNumber NVARCHAR(200),
        Court NVARCHAR(200),
        FilingDate NVARCHAR(200),
        OpposingParty NVARCHAR(200),
        [Status] NVARCHAR(200)
    );

    -- Insert data with optional status filter
    INSERT INTO #r_LegalCaseRegistry
    (
        CaseTitle,
        CaseNumber,
        Court,
        FilingDate,
        OpposingParty,
        [Status]
    )
    SELECT 
        l.CaseTitle,
        l.CaseNumber,
        l.CourtName AS Court,
        l.FilingDate,
        l.OpposingParty,
        l.[Status]
    FROM t_LegalCases AS l
    WHERE (@StatusFilter = 'All' OR l.[Status] = @StatusFilter)

    -- Return results
    SELECT * FROM #r_LegalCaseRegistry;

    -- Clean up
    DROP TABLE #r_LegalCaseRegistry;
END



--EXEC r_LegalCaseRegistry