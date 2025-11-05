<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("create   PROCEDURE [dbo].[r_ContractedDrivers](
    @ContractStartDate Date,
    @ContractEndDate Date
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #ContractedDrivers
    (
        DriverNo         VARCHAR(200),
        [Name]           VARCHAR(200),
        IDNo             INT,
        Phone            VARCHAR(200),
        Company          VARCHAR(200),
        LicenseNO        VARCHAR(200),
        ContractedPeriod Varchar(200)
        --Active     VARCHAR(200)

    );

    INSERT INTO #ContractedDrivers
    (DriverNo,
     [Name],
     IDNo,
     Phone,
     Company,
     LicenseNO,
     ContractedPeriod
        --Active
    )
    SELECT c.DriverNo,
           c.FullName,
           c.NationalID,
           c.Phone,
           'Deprecated >>',--c.CompanyName,
           'c.LicenseNumber',
           CONCAT(c.ContractStartDate, '-', c.ContractEndDate) AS ContractedPeriod
    --cd.Description AS Active

    FROM t_ContractedDrivers AS c
    WHERE (@ContractStartDate IS NULL OR c.ContractStartDate >= @ContractStartDate)
      AND (@ContractEndDate IS NULL OR c.ContractEndDate <= @ContractEndDate)


    SELECT * FROM #ContractedDrivers;

    DROP TABLE #ContractedDrivers;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_ContractedDrivers");
    }
};
