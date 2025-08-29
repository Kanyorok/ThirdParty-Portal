CREATE OR ALTER PROC [dbo].[r_TenderCommittees] @FromDate DATETIME = NULL,
                                                @ToDate DATETIME = NULL
AS
BEGIN

    CREATE TABLE #TenderCommittees
    (

        TenderID        VARCHAR(100),
        CommitteeName   NVARCHAR(200),
        AppointmentDate DATE,
        IsActive        VARCHAR(10),
        CreatedBy       VARCHAR(200),
        CreatedOn       DATE


    )

    INSERT INTO #TenderCommittees
    SELECT T.TenderNo as TenderID,
           TC.CommitteeName,
           TC.AppointmentDate,
           TC.IsActive,
           U.Name     as CreatedBy,
           TC.CreatedOn

    FROM t_tendercommittee TC

             JOIN t_tenders T ON T.ID = TC.TenderID
             JOIN t_users U ON U.ID = TC.CreatedBy


    WHERE (@FromDate IS NULL OR TC.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR TC.CreatedOn < DATEADD(DAY, 1, @ToDate))
    SELECT * FROM #TenderCommittees

END
--GO
