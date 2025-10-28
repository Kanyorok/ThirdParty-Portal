Create OR ALTER proc [dbo].[r_ProcurementModes] @FromDate smalldatetime = null,
                                                @ToDate smalldatetime = null
AS
BEGIN
    Create table #ProcurementModes
    (
        Name        VARCHAR(15),
        Description VARCHAR(30),
        UniqueCode  NVARCHAR(10),
        CreatedBy   VARCHAR(15),
        CreatedOn   DATE

    )
    INSERT INTO #ProcurementModes
    SELECT P.Name,
           P.Description,
           P.UniqueCode,
           U.Name,
           P.CreatedOn
    FROM t_ProcurementModes P
             JOIN
         t_users U ON U.ID = P.CreatedBy

    WHERE (@FromDate IS NULL OR P.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR P.CreatedOn < DATEADD(DAY, 1, @ToDate))

    SELECT * FROM #ProcurementModes;
END
--GO
