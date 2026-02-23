CREATE   OR ALTER  PROCEDURE r_DebtorInvoices 
@FromDate Date=null,
@ToDate Date=null,
@CurrencyID Nvarchar(50)=null
AS  
BEGIN  
    CREATE TABLE #DebtorInvoices
    (  
        RequestNumber       NVARCHAR(200),  
        RequestingModule    VARCHAR(200),  
        CustomerName     	VARCHAR(200),  
        Currency            NVARCHAR(50),  
        InvoiceNumber       NVARCHAR(200),  
        InvoiceDate         DATE,  
        DueDate             DATE,  
        AmountDue           MONEY,  
      	PaymentDate         DATE,  
   	    AmountPaid          MONEY,  
        CreditApplied       VARCHAR(50),  
        AppliedDate         DATE,  
        CreditLimit         MONEY  
    );  
  
    -- To get the latest transaction from t_financialtransactions 
    ;WITH LatestTransaction
 AS  
    (  
        SELECT  
            FT.ReferenceNumber AS InvoiceNumber,  
            FT.TransactionDate,  
            ROW_NUMBER() OVER (PARTITION BY FT.ReferenceNumber ORDER BY FT.TransactionDate DESC) AS rn  
        FROM t_FinancialTransactions FT  
    ),  
    -- To get a single  Credit Management Record for a particular customer  from t_financecreditmanagement
    SingleCredit AS  
    (  
        SELECT  
            E.CustomerID,  
            E.CreditLimit,  
            ROW_NUMBER() OVER (PARTITION BY E.CustomerID ORDER BY E.CreditLimit DESC) AS rn  
        FROM t_FinanceCreditManagement E  
    )  
  
    INSERT INTO #DebtorInvoices
    SELECT  
        FI.RequestID,  
        M.Name AS RequestingModule,  
        T.ThirdPartyName AS

		CustomerName,  
        C.Code AS Currency,  
        FI.InvoiceNumber,  
        FI.InvoiceDate,  
        FI.DueDate,  
        FI.TotalAmount,  
        LT.TransactionDate AS PaymentDate,  
        FI.AmountPaid, 
CASE 
WHEN FI.UseCredit = 1 
THEN 'Yes'
WHEN FI.UseCredit = 0 THEN 'No'
ELSE 'Unknown'
END AS CreditApplied ,
        FI.CreditAppliedOn,  
        SC.CreditLimit  
    FROM t_financeinvoices FI  


LEFT JOIN t_modules M        ON FI.ModuleID   = M.ModuleID
LEFT JOIN t_ThirdParties T  ON FI.CustomerID = T.ID
LEFT JOIN t_Currencies C    ON FI.CurrencyID = C.ID
LEFT JOIN LatestTransaction LT  
       ON LT.InvoiceNumber = FI.InvoiceNumber AND LT.rn = 1
LEFT JOIN SingleCredit SC 
       ON SC.CustomerID = FI.CustomerID AND SC.rn = 1
WHERE 
 (@FromDate IS NULL OR FI.InvoiceDate >= @FromDate)
 AND (@ToDate IS NULL OR FI.InvoiceDate < DATEADD(DAY, 1, @ToDate))
 AND (
        @CurrencyID IS NULL
        OR @CurrencyID = 'ALL'
        OR LTRIM(RTRIM(C.Code)) IN
           (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(@CurrencyID, ','))
     );

  
    SELECT * FROM #DebtorInvoices;  
END;