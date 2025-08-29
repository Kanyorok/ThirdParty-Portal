CREATE OR ALTER PROC [dbo].[r_PropertyReceipt] @FromDate SMALLDATETIME = NULL,
                                               @ToDate SMALLDATETIME = NULL
AS
BEGIN

    SET NOCOUNT ON

    CREATE TABLE #PropertyReceipt
    (
        Invoice       NVARCHAR(50),
        InvoiceDate   SMALLDATETIME,
        RentAmount    MONEY,
        BillingMonth  NVARCHAR(50),
        ServiceCharge MONEY,
        OtherCharges  MONEY,
        ParkingFee    MONEY,
        TotalDue      MONEY,
        Balance       MONEY,
        PaymentDate   SMALLDATETIME,
        PaymentMethod NVARCHAR(100),
        ReferenceNo   NVARCHAR(100),
        Remarks       NVARCHAR(300),
        AmountPaidNow MONEY,
--Status			CHAR(50),
        CreatedBy     NVARCHAR(50)
    )

    INSERT INTO #PropertyReceipt
    (Invoice,
     InvoiceDate,
     RentAmount,
     BillingMonth,
     ServiceCharge,
     OtherCharges,
     ParkingFee,
     TotalDue,
     Balance,
     PaymentDate,
     PaymentMethod,
     ReferenceNo,
     Remarks,
     AmountPaidNow,
--Status,
     CreatedBy)


    SELECT InvoiceNumber     AS [Invoice],
           RI.InvoiceDate    AS [Invoice Date],
           RI.RentAmount     AS [Rent Amount],
           RI.BillingMonth   AS [Billing Month],
           RI.ServicesCharge AS [Service Charge],
           RI.OtherCharges   AS [Other Charges],
           RI.ParkingFee     AS [Parking Fee],
           RR.TotalDue       AS [Total Due],
           RR.Balance,
           RR.PaymentDate    AS [Payment Date],
           C.Description     AS [Payment Method],
           RR.ReferenceNo    AS [Reference No.],
           RR.Remarks,
           RR.AmountPaidNow  AS [Amount Paid Now],
           U.UserID          AS [Created By]

    FROM t_RentReceipt RR
             JOIN t_rentinvoice RI ON RR.InvoiceID = RI.ID
             JOIN t_CodeDetails C ON RR.PaymentMethod = C.ID
             JOIN t_Users U ON RI.CreatedBy = U.Id


    WHERE (@FromDate IS NULL OR RI.InvoiceDate >= @FromDate)
      AND (@ToDate IS NULL OR RI.InvoiceDate < DATEADD(DAY, 1, @ToDate))


    SELECT * FROM #PropertyReceipt

    SET NOCOUNT OFF

END

--GO
