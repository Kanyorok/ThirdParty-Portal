CREATE OR ALTER PROC [dbo].[r_LeaseAgreements]
AS
BEGIN
    CREATE TABLE #LeaseAgreements
    (
        LeaseNumber      VARCHAR(200),
        Tenant           VARCHAR(200),
        Property         VARCHAR(200),
        BlockID          VARCHAR(200),
        FloorID          VARCHAR(200),

        StartDate        DATE,
        EndDate          DATE,
        PaymentFrequency VARCHAR(200),
        MonthlyRent      DECIMAL(18, 2),
        Deposit          DECIMAL(18, 2),
        DueDay           INT,
        SpecialTerms     VARCHAR(510)
    )

    INSERT INTO #LeaseAgreements
    (LeaseNumber,
     Tenant,
     Property,
     BlockID,
     FloorID,
     StartDate,
     EndDate,
     PaymentFrequency,
     MonthlyRent,
     Deposit,
     DueDay,
     SpecialTerms)
    SELECT LC.LeaseNumber,
           TM.TenantName       AS Tenant,
           PT.PropertyTypeName AS Property,
           PB.BlockName        AS BlockID,
           PF.FloorLabel       AS FloorID,

           LC.StartDate,
           LC.EndDate,
           CD.Description      AS PaymentFrequency,
           LC.MonthlyRent,
           LC.Deposit,
           LC.DueDay,
           LC.SpecialTerms
    FROM t_LeaseCreation LC
             JOIN t_propertytype PT ON PT.ID = LC.PropertyID
             JOIN t_CodeDetails CD ON CD.ID = LC.PaymentFrequency
             JOIN t_PropertyBlock PB ON PB.ID = LC.BlockID
             JOIN t_PropertyFloor PF ON PF.ID = LC.FloorID
             JOIN t_TenantMaintenance TM ON TM.Id = LC.Tenant

    SELECT * FROM #LeaseAgreements;
END
--GO

--EXEC r_LeaseAgreements
--GO
