create OR ALTER PROCEDURE [dbo].[r_License_Inspection_Schedule](
    @Status VARCHAR(100) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #LicenseInspectionSchedule
    (
        InspectionNo   VARCHAR(200),
        Vehicle        VARCHAR(200),
        InspectionType VARCHAR(200),
        InspectionDate DATE,
        DueDate        DATE,
        [Status]       VARCHAR(200),
        Remarks        VARCHAR(200)
    );

    INSERT INTO #LicenseInspectionSchedule
    (InspectionNo,
     Vehicle,
     InspectionType,
     InspectionDate,
     DueDate,
     [Status],
     Remarks)
    SELECT i.InspectionNo,
           v.RegistrationNo AS VehicleID,
           i.InspectionType,
           i.InspectionDate,
           i.DueDate,
           cd.Description   AS [Status],
           i.Remarks
    FROM t_FleetInspectionSchedule AS i
             JOIN t_FleetVehicles AS v ON v.Id = i.VehicleID
             JOIN t_CodeDetails AS cd ON cd.ID = v.Status

    WHERE @Status IS NULL
       OR @Status = 'ALL'
       OR cd.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))

    SELECT * FROM #LicenseInspectionSchedule;

    DROP TABLE #LicenseInspectionSchedule;
END

--GO
