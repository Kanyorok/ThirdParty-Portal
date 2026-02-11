CREATE OR ALTER  PROCEDURE R_TaxManagementCreditors
@FromDate Date,
@ToDate   Date
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #Taxmanagementcreditors
    (	
        InvoiceNumber	    NVARCHAR(100),
        SupplierName	    VARCHAR(200),
        Currency		    NVARCHAR(50),
		InvoiceDate			DATE,
		InvoiceAmount	    MONEY,
		TaxType			    NVARCHAR(100),
        TaxedAmount		    MONEY,
        TaxPercentage	    INT,
		TotalAmountDue		MONEY,
        AmountPaid		    MONEY,
        TaxAmountPaid	    MONEY,
        PaidDate		    DATE
    );

	INSERT INTO #Taxmanagementcreditors
	SELECT

	FE.InvoiceNumber,
	TP.ThirdPartyName,
	C.Code,
	FE.InvoiceDate,
	FE.InvoiceAmount,
	FT.TaxTypeName,
	FE.TaxAmount,
	FE.TaxPercentage,
	FE.TotalAmount,
	SUM(CASE WHEN T.DRCR = 'CR' THEN T.Amount ELSE 0 END) AS AmountPaid,
	  -- Tax portion of amount paid
        ISNULL(FE.TaxPercentage * ISNULL(SUM(T.Amount), 0) / 100, 0) AS TaxAmountPaid,
	 MAX(T.TransactionDate) AS PaidDate

	from t_financeinvoiceentry FE
	LEFT JOIN t_FinancialTransactions T
        ON FE.InvoiceNumber = T.ReferenceNumber
       AND T.DRCR = 'CR' 
	   LEFT JOIN t_FinanceTaxType FT
           ON FE.TaxID = FT.ID
    LEFT JOIN t_ThirdParties TP 
           ON FE.SupplierID = TP.ID  
		    LEFT JOIN t_Currencies C 
           ON FE.CurrencyID = C.ID
		    WHERE 
	 (@FromDate IS NULL OR FE.InvoiceDate >=@FromDate)
		 AND (@ToDate IS NULL OR FE.InvoiceDate < DATEADD(DAY, 1, @ToDate)) 
    GROUP BY
	FE.InvoiceNumber,
	TP.ThirdPartyName,
	C.Code,
	FE.InvoiceDate,
	FE.InvoiceAmount,
	FT.TaxTypeName,
	FE.TaxAmount,
	FE.TaxPercentage,
	FE.TotalAmount

	SELECT * FROM #Taxmanagementcreditors

	END
