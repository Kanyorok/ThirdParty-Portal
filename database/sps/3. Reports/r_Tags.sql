Create OR ALTER Procedure [dbo].[r_Tags] @FromDate smalldatetime=null,
                                         @ToDate smalldatetime = null
AS
BEGIN
    CREATE TABLE #Tags
    (
        TagID       Varchar(100),
        Name        Varchar(200),
        Visibility  Varchar(100),
        Description Varchar(100),
        CreatedBy   Varchar(100),
        CreatedOn   Date

    )

    Insert Into #Tags
    Select DT.TagID,
           DT.Name,
           DT.Visibility,
           DT.Description,
           U.Name as CreatedBy,
           DT.CreatedOn

    From t_DMSTags DT
             Join t_Users U ON U.ID = DT.Id

    Where (@FromDate IS Null or DT.CreatedOn >= @FromDate)
      AND (@ToDate IS Null or DT.CreatedOn < DATEADD(Day, 1, @ToDate))

    Select * from #Tags;
END
--GO
