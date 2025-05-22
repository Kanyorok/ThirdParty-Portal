CREATE PROCEDURE p_AddPurchaseOrderSum
    @OrderId BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
@TotPriceIncl FLOAT,
        @TotPriceExcl FLOAT,
        @TotTax FLOAT,
        @TotDiscAmnt FLOAT;

    -- Calculate totals from order lines
SELECT
    @TotPriceExcl = SUM((y.fQuantity * y.fUnitPriceExcl) - y.fLineDiscount),
    @TotTax = SUM(((y.fQuantity * y.fUnitPriceExcl) - y.fLineDiscount) * (y.fTaxRate / 100.0)),
    @TotPriceIncl = SUM(((y.fQuantity * y.fUnitPriceExcl) - y.fLineDiscount) * (1 + (y.fTaxRate / 100.0))),
    @TotDiscAmnt = SUM(y.fLineDiscount)
FROM t_OrderLines y
WHERE y.iOrderID = @OrderId;

-- Update the main order totals
UPDATE r
SET
    r.OrdDiscAmnt = @TotDiscAmnt,
    r.OrdTotExcl = @TotPriceExcl,
    r.OrdTotIncl = @TotPriceIncl,
    r.OrdTotTax = @TotTax
    FROM t_Orders r
WHERE r.Id = @OrderId;

SET NOCOUNT OFF;
END
