CREATE OR ALTER PROC [dbo].[r_FuelTypes]
   
AS
BEGIN
    SET NOCOUNT ON;

    SELECT 
        f.FuelName,
        f.[Description],
        f.IsActive,
        u.UserID AS CreatedBy
    FROM t_FuelTypes AS f
    JOIN t_Users AS u ON u.Id = f.CreatedBy

END



EXEC [r_FuelTypes]