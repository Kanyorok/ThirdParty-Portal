CREATE or ALTER PROCEDURE p_AddPurchaseOrderSum @OrderId BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @TotPriceIncl FLOAT,
        @TotPriceExcl FLOAT,
        @TotTax FLOAT,
        @TotDiscAmnt FLOAT,
        @TotalBeforeDiscount FLOAT,
        @DiscountPercent FLOAT;

    -- Calculate totals from order lines
    -- We store LineTotal on each line as: ((Qty * UnitPriceExcl) - LineDiscount) * (1 + TaxRate/100)
    -- Use LineTotal to derive totals to avoid double-counting or mismatched discount handling.
    SELECT @TotPriceIncl = SUM(ISNULL(y.LineTotal, 0)),
        @TotDiscAmnt = SUM(ISNULL(y.fLineDiscount, 0)),
        @TotalBeforeDiscount = SUM(ISNULL(y.fQuantity,0) * ISNULL(y.fUnitPriceExcl,0)),
        -- Derive exclusive totals and tax by reversing the tax component per line
        @TotPriceExcl = SUM(CASE WHEN ISNULL(y.fTaxRate,0) = 0 THEN ISNULL(y.LineTotal,0)
                    ELSE ISNULL(y.LineTotal,0) / (1 + (y.fTaxRate / 100.0)) END),
        @TotTax = SUM(CASE WHEN ISNULL(y.fTaxRate,0) = 0 THEN 0
                  ELSE ISNULL(y.LineTotal,0) - (ISNULL(y.LineTotal,0) / (1 + (y.fTaxRate / 100.0))) END)
    FROM t_OrderLines y
    WHERE y.iOrderID = @OrderId;

-- Calculate discount percentage (avoid divide-by-zero)
    SET @DiscountPercent = CASE
                               WHEN @TotalBeforeDiscount > 0 THEN (@TotDiscAmnt / @TotalBeforeDiscount) * 100
                               ELSE 0
        END;


-- Update the main order totals
    UPDATE r
    SET r.OrdDiscAmnt = @TotDiscAmnt,
        r.OrdTotExcl  = @TotPriceExcl,
        r.OrdTotIncl  = @TotPriceIncl,
        r.OrdTotTax   = @TotTax
    FROM t_Orders r
    WHERE r.Id = @OrderId;

    SET NOCOUNT OFF;
END
