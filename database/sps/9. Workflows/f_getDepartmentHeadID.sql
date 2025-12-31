CREATE OR ALTER FUNCTION dbo.f_getDepartmentHeadID (@UserID INT)
RETURNS INT
AS
BEGIN
    DECLARE @HeadID INT;

    SELECT @HeadID = COALESCE(d.HeadId, d.DeputyHeadId)
    FROM t_users u WITH (NOLOCK)
    JOIN t_Employees e WITH (NOLOCK) ON u.EmployeeId = e.Id
    JOIN t_Departments d WITH (NOLOCK) ON e.DepartmentId = d.Id
    WHERE u.id = @UserID;

    RETURN @HeadID;
END;
