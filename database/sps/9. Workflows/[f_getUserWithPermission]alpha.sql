CREATE OR ALTER FUNCTION [dbo].[f_getUserWithPermission](@permissionId BIGINT)
RETURNS TABLE
AS
RETURN
(
    SELECT DISTINCT u.Id, u.Name, u.Email
    FROM t_Users u
    WHERE u.DeletedOn IS NULL
      AND u.Id NOT IN (1, 2) -- Exclude System Users (ERPSYS, CSADM)
      AND (
          -- Via roles
          EXISTS (
              SELECT 1
              FROM t_ModelRoles mr
              INNER JOIN t_RolePermissions rp ON mr.role_id = rp.role_id
              WHERE CAST(mr.model_id AS BIGINT) = u.Id
                AND mr.model_type = 'UserID'
                AND rp.permission_id = @permissionId
          )
          OR
          -- Direct permission
          EXISTS (
              SELECT 1
              FROM t_ModelPermissions mp
              WHERE CAST(mp.model_id AS BIGINT) = u.Id
                AND mp.model_type = 'UserID'
                AND mp.permission_id = @permissionId
          )
      )
);
