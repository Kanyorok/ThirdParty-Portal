alter PROCEDURE p_AddPurchaseOrderLines
    @Item bigint,
    @Quantity float = 0,
    @Price float = 0,
    @Tax float = 0,
    @Discount float = 0,
    @LineTotal float = 0,
    @User bigint,
    @OrderId bigint,
    @BranchId bigint =0

    AS
BEGIN
    SET NOCOUNT ON


    DECLARE @PriceIncl float, @LineTotal float;

    set @PriceIncl = isnull(@Price,0) * isnull(@Tax,0)

--     set @LineTotal = ((@Quantity * @Price) - @Discount ) * (1 + (y.fTaxRate / 100.0))



    -- Insert new purchase order lines
    INSERT INTO t_OrderLines (iOrderID, fQuantity, fUnitPriceExcl, fUnitPriceIncl, flineDiscount, fTaxRate, CreatedBy,CreatedOn, ModifiedBy,ModifiedOn,BranchID,iStockCodeID, LineTotal)
    VALUES( @OrderId,@Quantity,@Price,@PriceIncl,@Discount,@Tax,@User, getdate(),@User,getdate(), @BranchId, @Item, @LineTotal)




    SET NOCOUNT OFF
END
