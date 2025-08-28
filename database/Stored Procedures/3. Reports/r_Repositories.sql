CREATE OR ALTER PROCEDURE [dbo].[r_Repositories](
    @FromDate smallDatetime = null,
    @ToDate smallDatetime=null
)
AS
BEGIN
    CREATE TABLE #Repositories
    (
        DocumentId   VARCHAR(200),
        Name         VARCHAR(200),
        MimeType     VARCHAR(200),
        RepositoryId INT,
        VersionNo    INT,
        Size         BIGINT,
        Visibility   VARCHAR(200),
        CreatedBy    VARCHAR(200),
        CreatedOn    DATE,
        ModifiedBy   VARCHAR(200),
        ModifiedOn   DATE
    )

    INSERT INTO #Repositories
    (DocumentId,
     Name,
     MimeType,
     RepositoryId,
     VersionNo,
     Size,
     Visibility,
     CreatedBy,
     CreatedOn,
     ModifiedBy,
     ModifiedOn)
    SELECT DR.DocumentId,
           DR.Name,
           D.MimeType,
           D.RepositoryId,
           DR.Version AS VersionNo,
           DR.Size,
           D.Visibility,
           U.Name     AS CreatedBy,
           DR.CreatedOn,
           U.Name     AS ModifiedBy,
           DR.ModifiedOn
    FROM t_DocumentVersions DR
             JOIN t_Documents D ON D.Id = DR.DocumentId
             JOIN t_Users U ON U.Id = DR.CreatedBy
    Where (@FromDate IS NULL OR DR.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR DR.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND D.DeletedOn is null
    order by D.CreatedOn desc


    SELECT * FROM #Repositories
END


GO
