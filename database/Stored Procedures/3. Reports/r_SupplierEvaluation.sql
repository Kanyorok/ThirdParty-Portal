CREATE or ALTER PROC [dbo].[r_SupplierEvaluation] @FromDate smalldatetime=null,
                                                  @ToDate smalldatetime= null
AS
BEGIN
    CREATE TABLE #SupplierEvaluation
    (
        CommitteeMemberName VARCHAR(300),
        UserCode            VARCHAR(200),
        RFQID               VARCHAR(300),
        RFQComment          VARCHAR(500),
        Confirmation        INT,
        CreatedBy           VARCHAR(200),
        CreatedOn           DATE
    )

    INSERT INTO #SupplierEvaluation
    SELECT E.CommitteeMemberName,
           E.UserCode,
           L.ItemName as RFQID,
           E.RFQComment,
           E.Confirmation,
           U.Name     as CreatedBy,
           E.CreatedOn

    FROM t_RFQEvaluations E
             JOIN t_RFQLines L ON L.RFQId = E.RFQID
             JOIN t_users U ON U.ID = E.CreatedBy

    WHERE (@FromDate IS NULL OR E.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR E.CreatedOn < DATEADD(DAY, 1, @ToDate))


    SELECT * from #SupplierEvaluation;

END
GO
