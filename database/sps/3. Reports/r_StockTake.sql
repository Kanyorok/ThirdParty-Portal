CREATE OR ALTER PROCEDURE [dbo].[r_StockTake](
    @FromDate SMALLDATETIME = NULL,
    @ToDate SMALLDATETIME = NULL
)
AS
BEGIN
    SET NOCOUNT ON
    CREATE TABLE #StockTake
    (
        Branch    VARCHAR(200),
        Store     VARCHAR(200),
        CountedBy VARCHAR(200),
        Date      DATETIME,
        PostedBy  VARCHAR(200),
        PostedOn  DATETIME
    )

    INSERT INTO #StockTake
    (Branch,
     Store,
     CountedBy,
     Date,
     u.PostedBy,
     PostedOn)
    SELECT BR.Name,
           K.StoreId,
           ST.CountedBy,
           ST.CountDate,
           U.Name,
           ST.CreatedOn
    FROM t_StockTake ST
             JOIN t_Branches BR ON ST.BRANCHID = BR.ID
             JOIN t_Users U on U.ID = ST.CREATEDBY
             JOIN t_Stores K on K.Id = st.storeid

    WHERE (@FromDate IS NULL OR ST.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR ST.CreatedOn < DATEADD(DAY, 1, @ToDate))
    SELECT * FROM #StockTake

    DROP TABLE #StockTake

    SET NOCOUNT OFF
END

--GO
