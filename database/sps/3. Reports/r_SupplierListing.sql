CREATE OR ALTER PROCEDURE [dbo].[r_SupplierListing]
AS
BEGIN

    CREATE TABLE #SupplierListing
    (
        Id           nvarchar(50),
        SupplierName varchar(100),
        ContactEmail varchar(150),
        ContactPhone varchar(25),
        Address      varchar(100),
        CategoryId   varChar(200),
        CreatedBy    varChar(20),
        ModifiedBy   varchar(50),
        CreatedOn    date
    )
    INSERT INTO #SupplierListing
    select S.Id,
           'S.SupplierName',
           'S.ContactEmail',
           'S.ContactPhone',
           'S.Address',
           I.Name as CategoryId,
           U.Name as CreatedBy,
           U.Name as ModifiedBy,
           S.CreatedOn

    from t_Suppliers S
             JOIN t_Users U ON U.Id = S.CreatedBy
             JOIN t_ItemCategories I ON I.ID = S.CategoryId

    select * from #SupplierListing

END
--GO
