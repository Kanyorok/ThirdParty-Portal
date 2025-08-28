cREATE OR ALTER PROC [dbo].[r_TenantMaintenance]
AS
BEGIN
    CREATE TABLE #TenantMaintenance
    (
        TenantType  VARCHAR(200),
        TenantName  VARCHAR(200),
        ID          VARCHAR(200),
        Phone       VARCHAR(200),
        Email       VARCHAR(200),
        Remarks     VARCHAR(200),
        Nationality VARCHAR(200)
    )

    INSERT INTO #TenantMaintenance (TenantType,
                                    TenantName,
                                    ID,
                                    Phone,
                                    Email,
                                    Remarks,
                                    Nationality)
    SELECT CD.Description AS TenantType,
           TM.TenantName,
           TM.IDRegistrationNo,
           TM.PhoneNumber,
           TM.EmailAddress,
           TM.Remarks,
           TM.Nationality
    FROM t_TenantMaintenance AS TM
             JOIN t_CodeDetails CD ON TM.TenantType = CD.ID


    SELECT * FROM #TenantMaintenance;

    DROP TABLE #TenantMaintenance;
END
GO
