CREATE OR ALTER PROC [dbo].[r_RequisitionsListing] @FromDate smalldatetime =null,
                                                   @ToDate smalldatetime = null,
                                                   @BranchID VARCHAR(200) =null,
                                                   @DepartmentID VARCHAR(200)=null
AS
BEGIN

    CREATE TABLE #RequisitionListing
    (
        RequisitionNo VARCHAR(20),
        BranchID      VARCHAR(200),
        DepartmentID  VARCHAR(200),
        StatusID      VARCHAR(20),
        CreatedBy     VARCHAR(20),
        CreatedOn     DATE
    )

    INSERT INTO #RequisitionListing
    SELECT R.RequisitionNo,
           B.Name        as BranchID,
           D.Name        as DepartmentID,
           C.Description as StatusID,
           U.Name        as CreatedBy,
           R.CreatedOn

    FROM t_Requisitions R
             JOIN
         t_Users U ON U.Id = R.CreatedBy
             JOIN
         t_Branches B ON B.ID = R.BranchID
             JOIN
         t_Departments D ON D.ID = R.DepartmentID
             JOIN t_CodeDetails C on C.ID = R.StatusID

    WHERE (@FromDate IS NULL OR R.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR R.CreatedOn < DATEADD(DAY, 1, @ToDate))


      AND (@BranchID IS NULL OR @BranchID = 'ALL' OR B.Name IN (SELECT value FROM STRING_SPLIT(@BranchID, ',')))
      AND (@DepartmentID IS NULL OR @DepartmentID = 'ALL' OR
           D.Name IN (SELECT value FROM STRING_SPLIT(@DepartmentID, ',')));

    SELECT * FROM #RequisitionListing;
END
--GO
