CREATE OR ALTER PROCEDURE [dbo].[r_RFQresponse]
AS
BEGIN


    CREATE TABLE #RFQresponse
    (
        RFQNumber    NVARCHAR(50),
        SupplierName VARCHAR(100),
        TotalPayable MONEY,
        DurationDays VARCHAR(20),
        CreatedBy    NVARCHAR(50)

    )

    INSERT INTO #RFQresponse

    SELECT R.RFQNumber,
           R.SupplierName,
           R.TotalPayable,
           R.DurationDays,
           U.Name

    FROM t_RFQResponse R
             JOIN t_Users U on u.Id = R.CreatedBy

    SELECT * FROM #RFQresponse

END

--go
--exec r_RFQresponse

--GO
