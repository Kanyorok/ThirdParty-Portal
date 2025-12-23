--created  @mureithi.maina - 03 Sep 2025
CREATE OR ALTER FUNCTION f_getUserWithPermission(@permissionId INT)
    RETURNS TABLE
        AS
        RETURN(select [t_Users].[Id]
               from [t_Users]
               where (
                   exists (select *
                           from [t_Roles]
                                    inner join [t_ModelRoles] on [t_Roles].[id] = [t_ModelRoles].[role_id]
                           where [t_Users].[Id] = [t_ModelRoles].[model_id]
                             and [t_ModelRoles].[model_type] = 'UserID'
                             and exists (select *
                                         from [t_Permissions]
                                                  inner join [t_RolePermissions]
                                                             on [t_Permissions].[id] = [t_RolePermissions].[permission_id]
                                         where [t_Roles].[id] = [t_RolePermissions].[role_id]
                                           and [t_Permissions].[id] = @permissionId)) or exists (select *
                                                                                                 from [t_Permissions]
                                                                                                          inner join [t_ModelPermissions]
                                                                                                                     on [t_Permissions].[id] = [t_ModelPermissions].[permission_id]
                                                                                                 where [t_Users].[Id] = [t_ModelPermissions].[model_id]
                                                                                                   and [t_ModelPermissions].[model_type] = 'UserID'
                                                                                                   and [t_Permissions].[id] = @permissionId))
                 and [t_Users].[DeletedOn] is null
                 and t_Users.Id <> 1);

