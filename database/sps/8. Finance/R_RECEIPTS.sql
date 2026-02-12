CREATE  PROCEDURE R_RECEIPTS
	@FROMDATE DATE,
	@TODATE	 DATE
AS
BEGIN

	CREATE TABLE #RECEIPTS
	(
	
	ReceiptNumber			NVARCHAR(100),
	CustomerName			VARCHAR(100),
	ReceiptDate				DATE,
	AmountReceived			DECIMAL(18,2),
	PaymentMethod			NVARCHAR(100),
	ReferenceNumber			NVARCHAR(100),
	ValueDate				DATE,
	PostingDate				DATE

	)

	INSERT INTO #RECEIPTS
	SELECT 
	R.ReceiptNumber,
	T.ThirdPartyName,
	ReceiptDate,
	AmountReceived,
	PaymentMethod,
	ReferenceNumber,
	R.ValueDate,
	PostingDate

	FROM T_FINANCERECEIPTS R

	JOIN T_THIRDPARTIES T ON R.CustomerID =T.ID
	 WHERE 
	 (@FromDate IS NULL OR R.ValueDate >=@FromDate)
		 AND (@ToDate IS NULL OR R.ValueDate < DATEADD(DAY, 1, @ToDate))      
	
	SET NOCOUNT ON;

    SELECT * FROM #RECEIPTS
END;
GO
--exec  R_RECEIPTS
--	@FROMDATE ='01 Jan 2026',
--	@TODATE	 ='07 Jan 2026'

