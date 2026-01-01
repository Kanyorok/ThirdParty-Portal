CREATE OR ALTER FUNCTION [dbo].[f_CheckUserPermission](
    @UserID BIGINT,
    @PermissionId BIGINT
)
RETURNS BIT
AS
BEGIN
    DECLARE @Result BIT = 0

    IF EXISTS (
        SELECT 1
        FROM [t_Users] u
        WHERE u.Id = @UserID
          AND u.DeletedOn IS NULL
          AND (
              -- Permission via roles
              EXISTS (
                  SELECT 1
                  FROM [t_ModelRoles] mr
                  INNER JOIN [t_RolePermissions] rp ON mr.role_id = rp.role_id
                  WHERE mr.model_id = u.Id
                    AND mr.model_type = 'UserID'
                    AND rp.permission_id = @PermissionId
              )
              OR
              -- Direct permission assignment
              EXISTS (
                  SELECT 1
                  FROM [t_ModelPermissions] mp
                  WHERE mp.model_id = u.Id
                    AND mp.model_type = 'UserID'
                    AND mp.permission_id = @PermissionId
              )
          )
    )
    BEGIN
        SET @Result = 1
    END

    RETURN @Result
END
GO
