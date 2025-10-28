-- Created @teresanyaata on August 29 2025
CREATE or alter FUNCTION dbo.fn_GetBudgetTotalAmount(
    @FromDate smalldatetime = NULL,
    @ToDate smalldatetime = NULL
)
    RETURNS DECIMAL(18, 2)
AS
BEGIN
    DECLARE @Total DECIMAL(18, 2)

    SELECT @Total = SUM(Amount)
    FROM t_BudgetManualEntry
    WHERE (@FromDate IS NULL OR CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR CreatedOn < DATEADD(DAY, 1, @ToDate))

    RETURN ISNULL(@Total, 0)
END
