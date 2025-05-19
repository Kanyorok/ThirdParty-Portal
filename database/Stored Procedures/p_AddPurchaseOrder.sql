alter PROCEDURE p_AddPurchaseOrder
    @RfqNo bigint,
    @OrderDate date,
    @Terms varchar(255),
    @Priority date,
    @BranchId int,
    @User int,
    @Supplier int
AS
BEGIN
    SET NOCOUNT ON

    DECLARE  @OrderId int, @OrderNo varchar(255);


    -- Insert new purchase order
    INSERT INTO t_Orders (OrderDate, Terms, Priority, ExtOrdNum, CreatedBy,CreatedOn, ModifiedBy,ModifiedOn,BranchID, AccountID)
    VALUES( isnull(@OrderDate,getdate()), @Terms, @Priority,  @RfqNo ,@User, getdate(),@User,getdate(), @BranchId,@Supplier)


--     -- Generate OrderNo
    SET @OrderId = SCOPE_IDENTITY();

    SET @OrderNo = (
        SELECT 'PO-' + RIGHT(REPLICATE('0', 4) + CAST(isnull(MAX(@OrderId),0) AS VARCHAR), 4)

    )
--

    UPDATE t set t.OrderNo = @OrderNo from t_Orders t where t.id=@OrderId

    SET NOCOUNT OFF
END
