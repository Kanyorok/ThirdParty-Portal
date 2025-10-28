CREATE or alter PROCEDURE p_AddPurchaseOrderLines @Item bigint,
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


    DECLARE @PriceIncl float;

    DECLARE @DiscAmount float;

    -- Unit price including tax
    set @PriceIncl = isnull(@Price, 0) * (1 + @Tax / 100)

    -- Calculate discount amount (absolute) based on percentage passed in @Discount
    set @DiscAmount = (@Quantity * isnull(@Price, 0)) * (isnull(@Discount, 0) / 100.0)

    -- Line total: (quantity * unit price) minus discount amount, then apply tax
    set @LineTotal = ((@Quantity * isnull(@Price, 0)) - @DiscAmount) * (1 + @Tax / 100)

    -- Insert new purchase order lines

    -- Store fLineDiscount as absolute discount amount (not percentage)
    INSERT INTO t_OrderLines (iOrderID, fQuantity, fUnitPriceExcl, fUnitPriceIncl, flineDiscount, fTaxRate, CreatedBy,
                              CreatedOn, ModifiedBy, ModifiedOn, BranchID, iStockCodeID, LineTotal)
    VALUES (@OrderId, @Quantity, @Price, @PriceIncl, @DiscAmount, @Tax, @User, getdate(), @User, getdate(), @BranchId,
            @Item, @LineTotal)


    SET NOCOUNT OFF
END
