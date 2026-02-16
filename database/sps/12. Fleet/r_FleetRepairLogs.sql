CREATE OR ALTER PROC [dbo].[r_FleetRepairLogs]
(
    @RepairType VARCHAR(MAX) = Null
)
AS
BEGIN
    SET NOCOUNT ON;
    
    CREATE TABLE #FleetRepairLogs               
    (                                  
        Repair      NVARCHAR(200),
        Vehicle     NVARCHAR(200),                               
        [Type]      NVARCHAR(200),
        RepairDate  Date,
        Vendor      NVARCHAR(200),
        Cost        DECIMAL(10,2),
        [Description] NVARCHAR(200)
    );  

    INSERT INTO #FleetRepairLogs
    (                                 
        Repair,
        Vehicle,                               
        [Type],
        RepairDate,
        Vendor,
        Cost,
        [Description]
    )
    SELECT 
        r.RepairID AS Repair,
        v.RegistrationNo AS Vehicle,
        c.[Description] AS [Type],
        r.RepairDate,
        t.ThirdPartyName AS Vendor,
        r.Cost,
        r.[Description]
    FROM t_FleetRepairLogs AS r
    JOIN t_FleetVehicles AS v ON v.Id = r.VehicleID
    JOIN t_CodeDetails AS c ON c.ID = r.RepairType
    JOIN t_ThirdParties AS t ON t.ID = r.VendorID  -- Fixed: Join on ID, not ThirdPartyName
    WHERE (
        @RepairType IS NULL
        OR @RepairType = 'ALL'
        OR c.Description IN (
            SELECT LTRIM(RTRIM(value))
            FROM STRING_SPLIT(@RepairType, ',')
        )
    );

    SELECT * FROM #FleetRepairLogs;
    DROP TABLE #FleetRepairLogs;
END
GO

-- EXEC [r_FleetRepairLogs]