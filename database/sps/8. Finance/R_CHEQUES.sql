
CREATE OR ALTER PROCEDURE R_CHEQUES
@FromDate DATE,
@ToDate	DATE

AS
BEGIN

CREATE TABLE #CHEQUELIST

	(
	ChequeID				INT,
	Direction				VARCHAR(100),
	ChequeNumber			NVARCHAR(100),
	ChequeDate				DATE,
	DueDate					DATE,
	Currency				NVARCHAR(50),
	Amount					DECIMAL(18,2),
	PartyType				VARCHAR(100),
	PartyName				VARCHAR(100),
	Status					VARCHAR(100),
	ReceivedDate			DATE,
	DepositDate				DATE
	
	)



	INSERT INTO #CHEQUELIST
	SELECT 
	ChequeID,
	Direction,
	ChequeNumber,
	ChequeDate,
	DueDate,
	C.Code,
	Amount,
	CASE 
	WHEN PartyType ='V' THEN 'Supplier'
	WHEN PartyType ='T' THEN 'Tenant'
	ELSE 'Unknown'
	END AS PartyType,
	PartyName,
	Status,
	ReceivedDate,
	DepositDate

 FROM t_Cheques CQ
 JOIN t_Currencies C ON CQ.CurrencyID=C.ID
	
	WHERE 
 (@FromDate IS NULL OR ChequeDate >= @FromDate)
 AND (@ToDate IS NULL OR ChequeDate < DATEADD(DAY, 1, @ToDate))
	SET NOCOUNT ON;

	SELECT * FROM #CHEQUELIST
  END

--GO

--EXEC  R_CHEQUES 
--@FromDate='01 Jan 2026',
--@ToDate='06 Jan 2026'

