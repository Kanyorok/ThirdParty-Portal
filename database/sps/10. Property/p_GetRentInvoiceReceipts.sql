CREATE OR ALTER PROCEDURE p_GetRentInvoiceReceipts
    @ReceiptStatus VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        FinInv.RequestID,
        FinRec.ReceiptNumber,
        ProInv.InvoiceNumber,
        ProInv.Lease,
        FinInv.ModuleID,
        FinInv.CurrencyID,
        FinInv.CustomerID,
        FinInv.TotalAmount,
        FinRec.AmountReceived
    FROM t_RentInvoice AS ProInv
    LEFT JOIN t_FinanceInvoices AS FinInv
        ON ProInv.RequestID = FinInv.RequestID
    LEFT JOIN t_FinanceReceiptAllocations AS FinRecAll
        ON FinInv.Id = FinRecAll.InvoiceID
    LEFT JOIN t_FinanceReceipts AS FinRec
        ON FinRecAll.ReceiptID = FinRec.Id
    WHERE FinInv.ModuleID = 500000
      AND FinRec.Status = @ReceiptStatus;
END;
GO
