CREATE OR ALTER PROCEDURE [dbo].[r_ContractedDrivers]
(
    @ContractStartDate DATE = NULL,
    @ContractEndDate   DATE = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT 
        c.DriverNo,
        c.FullName AS [Name],
        c.NationalID AS IDNo,
        c.Phone,
        t.ThirdPartyName AS Company,
        CONCAT(
            CONVERT(VARCHAR(10), c.ContractStartDate, 23),
            ' - ',
            CONVERT(VARCHAR(10), c.ContractEndDate, 23)
        ) AS ContractedPeriod
    FROM t_ContractedDrivers AS c
    INNER JOIN t_ThirdParties AS t
        ON c.CompanyID = t.Id   -- Make sure this is correct in your DB
    WHERE
        -- Overlapping contract logic
        (@ContractStartDate IS NULL OR c.ContractEndDate >= @ContractStartDate)
    AND (@ContractEndDate   IS NULL OR c.ContractStartDate <= @ContractEndDate)
    ORDER BY c.ContractStartDate;

END
GO


