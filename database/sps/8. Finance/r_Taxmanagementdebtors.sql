CREATE OR ALTER PROCEDURE r_Taxmanagementdebtors
@FromDate Date,
@ToDate   Date
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #Taxmanagementdebtors
    (	
        InvoiceNumber	    NVARCHAR(100),
        CustomerName	    VARCHAR(200),
        Currency		    NVARCHAR(50),
        SourceModule	    VARCHAR(100),
        TaxType			    NVARCHAR(100),
        TaxedAmount		    MONEY,
        TaxPercentage	    INT,
		InvoiceDate			Date,
        InvoiceAmount	    MONEY,
        AmountPaid		    MONEY,
        TaxAmountPaid	    MONEY,
        PaidDate		    DATE
    );

    INSERT INTO #Taxmanagementdebtors
    SELECT 
        FI.InvoiceNumber,
        TP.ThirdPartyName,
        C.Code,
        M.Name,
        T.TaxTypeName,
        FI.TaxAmount,
        FI.TaxPercentage,
		FI.InvoiceDate,
        FI.InvoiceAmount,

        -- Amount paid (defaults to 0 if no payment)
        ISNULL(SUM(FT.Amount), 0) AS AmountPaid,

        -- Tax portion of amount paid
        ISNULL(FI.TaxPercentage * ISNULL(SUM(FT.Amount), 0) / 100, 0) AS TaxAmountPaid,

        -- Last payment date (NULL if unpaid)
        MAX(FT.TransactionDate) AS PaidDate
    FROM t_financeinvoices FI
    LEFT JOIN t_FinancialTransactions FT
           ON FI.InvoiceNumber = FT.ReferenceNumber
          AND FT.DRCR = 'CR'
    LEFT JOIN t_Currencies C 
           ON FI.CurrencyID = C.ID
    LEFT JOIN t_Modules M 
           ON FI.ModuleID = M.ModuleID
    LEFT JOIN t_FinanceTaxType T 
           ON FI.TaxID = T.ID
    LEFT JOIN t_ThirdParties TP 
           ON FI.CustomerID = TP.ID  

		    WHERE 
	 (@FromDate IS NULL OR FI.InvoiceDate >=@FromDate)
		 AND (@ToDate IS NULL OR FI.InvoiceDate < DATEADD(DAY, 1, @ToDate)) 
    GROUP BY
        FI.InvoiceNumber,
        TP.ThirdPartyName,
        C.Code,
        M.Name,
        T.TaxTypeName,
        FI.TaxAmount,
		FI.InvoiceDate,
        FI.TaxPercentage,
        FI.InvoiceAmount;

    SELECT * 
    FROM #Taxmanagementdebtors;
END;

