CREATE OR ALTER PROC [dbo].[r_Permissions](
    @CreatedOn smalldatetime
)
AS
BEGIN

    Create table #Permissions

    (

        Name       VarChar(200),
        GuardName  Varchar(50),
        CreateDate smalldatetime
    )

    Insert Into #Permissions
    select name,
           guard_name,
           created_at
    from t_Permissions
    Where cast(created_at as date) = @CreatedOn


    select * from #Permissions

END
GO
