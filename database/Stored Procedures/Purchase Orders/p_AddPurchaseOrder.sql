alter PROCEDURE p_AddPurchaseOrder @Supplier bigint,
    @OrderDate date,
                                   @RfqNo bigint,
                                   @Priority varchar(20),
    @Terms varchar(255),
                                   @User bigint,
                                   @BranchId int = 0


AS
BEGIN
    SET NOCOUNT ON

    DECLARE  @OrderId int, @OrderNo varchar(255);


    -- Insert new purchase order
    INSERT INTO t_Orders (OrderDate, Terms, Priority, ExtOrdNum, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn, BranchID,
                          AccountID)
    VALUES (isnull(@OrderDate, getdate()), @Terms, @Priority, @RfqNo, @User, getdate(), @User, getdate(), @BranchId,
            @Supplier)


--     -- Generate OrderNo
    SET @OrderId = SCOPE_IDENTITY();

    SET @OrderNo = (SELECT 'PO-' + RIGHT(REPLICATE('0', 4) + CAST(isnull(@OrderId, 0) AS VARCHAR), 4)

    )
--

    UPDATE t set t.OrderNo = @OrderNo from t_Orders t where t.id=@OrderId

    SELECT @OrderId AS POID, @OrderNo AS OrderNo;

    SET NOCOUNT OFF
END



