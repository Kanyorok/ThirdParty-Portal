CREATE OR ALTER PROCEDURE [dbo].[r_documentVersions](@DocumentId NVARCHAR(100)) AS
BEGIN
    CREATE TABLE #DocumentVersions
    (
        Id          BIGINT,
        Name        NVARCHAR(255),
        Version     BIGINT,
        Path        TEXT,
        -- Checksum BIGINT,
        Size        BIGINT,
        Document    NVARCHAR(155),
        Description TEXT,
        CreatedBy   NVARCHAR(255),
        CreatedOn   NVARCHAR(155),
        ModifiedBy  NVARCHAR(255),
        ModifiedOn  NVARCHAR(155)
    )
    INSERT INTO #DocumentVersions
    (Id,
     Name,
     Version,
     Path,
        --  Checksum,
     Size,
     Document,
     Description,
     CreatedBy,
     CreatedOn,
     ModifiedBy,
     ModifiedOn)
    SELECT v.Id,
           v.Name,
           v.Version,
           v.Path,
           -- v.Checksum,
           v.Size,
           d.Name,
           v.Description,
           cu.Name,
           v.CreatedOn,
           mu.Name,
           v.ModifiedOn
    FROM t_DocumentVersions (NOLOCK) v
             LEFT JOIN t_Documents d ON v.DocumentId = d.Id
             LEFT JOIN t_Users cu ON v.CreatedBy = cu.Id
             LEFT JOIN t_Users mu ON v.ModifiedBy = mu.Id
    WHERE v.DeletedOn IS NULL
      AND v.DeletedBy IS NULL
      AND v.DocumentId = @DocumentId

    SELECT *
    FROM #DocumentVersions
END
GO
