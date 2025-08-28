CREATE OR ALTER PROC [dbo].[r_GoodsReceipts] @FromDate SMALLDATETIME =NULL,
                                             @ToDate SMALLDATETIME = NULL

--@StoreID    VARCHAR(200) = NULL
AS
BEGIN
    CREATE TABLE #GoodsReceipts
    (
        GRNID        VARCHAR(20),
        POID         VARCHAR(20),
        SupplierName VARCHAR(100),
        StoreID      VARCHAR(200),
        RecievedBy   VARCHAR(50),
        ItemNo       INT,
        POQTY        FLOAT,
        ReceivedQTY  FLOAT,
        TransferTo   VARCHAR(50),
        CreatedOn    DATE,
        CreatedBy    VARCHAR(50)
    )

    INSERT INTO #GoodsReceipts
    SELECT G.GRNID,
           G.POID,
           S.SupplierName,
           G.StoreId,
           U2.Name as ReceivedBy,
           G.ItemNo, -- Number of items
           G.POQTY,
           G.ReceivedQTY,
           U3.Name AS TransferTo,
           G.CreatedOn,
           U.Name  AS CreatedBy

    FROM t_GoodsReceipts G
             JOIN t_Suppliers S ON S.Id = G.SupplierId
             JOIN t_Users U ON U.Id = G.CreatedBy
             JOIN t_Users U2 ON U2.Id = G.ReceivedBy
             JOIN t_Users U3 ON U3.Id = G.TransferTo

    WHERE (@FromDate IS NULL OR G.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR G.CreatedOn < DATEADD(DAY, 1, @ToDate))
    ----AND G.StoreID IN (   ----   SELECT value    ----  FROM STRING_SPLIT(CASE    ----                    WHEN ISNULL(@StoreID, '') = 'ALL' THEN ''    ----                    ELSE @StoreID     ----                 END, ','))
    --


    SELECT * FROM #GoodsReceipts

END
--GO
--EXEC r_GoodsReceipts
GO
