CREATE OR ALTER PROC [dbo].[r_GoodsReceipts1] @FromDate SMALLDATETIME = NULL,
                                              @ToDate SMALLDATETIME = NULL,
                                              @InspectionStatus VARCHAR(100) = NULL,
                                              @StoreID VARCHAR(200) = NULL
AS
BEGIN
    CREATE TABLE #GoodsReceipts
    (
        GRNID            VARCHAR(20),
        POID             VARCHAR(20),
        SupplierName     VARCHAR(100),
        StoreID          VARCHAR(200),
        RecievedBy       VARCHAR(50),
        InspectionStatus VARCHAR(100),
        TransferStatus   VARCHAR(50),
        ItemNo           INT,
        POQTY            FLOAT,
        ReceivedQTY      FLOAT,
        TransferTo       VARCHAR(50),
        CreatedOn        DATE,
        CreatedBy        VARCHAR(50)
    );

    INSERT INTO #GoodsReceipts
    SELECT G.GRNID,
           G.POID,
           S.SupplierName,
           G.StoreId,
           U.Name AS ReceivedBy,
           G.InspectionStatus,
           G.TransferStatus,
           G.ItemNo,
           G.POQTY,
           G.ReceivedQTY,
           G.TransferTo,
           G.CreatedOn,
           U.Name AS CreatedBy
    FROM t_GoodsReceipts G
             JOIN t_Suppliers S ON S.Id = G.SupplierId
             JOIN t_Users U ON U.Id = G.CreatedBy
    -- If needed: LEFT JOIN t_Users U2 ON U2.Id = G.ReceivedBy

    WHERE (@FromDate IS NULL OR G.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR G.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (
        @StoreID = 'ALL' OR
        G.StoreID IN (SELECT LTRIM(RTRIM(value))
                      FROM STRING_SPLIT(@StoreID, ','))
        )
      AND (
        @InspectionStatus = 'ALL' OR
        G.InspectionStatus IN (SELECT LTRIM(RTRIM(value))
                               FROM STRING_SPLIT(@InspectionStatus, ','))
        );

    SELECT * FROM #GoodsReceipts;
END;
GO
