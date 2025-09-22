--created @bii.hezron - 18 Aug 2025
--modified @mureithi.maina - 28 Aug 2025
CREATE OR ALTER FUNCTION f_child_repositories(@repository_id INT)

    RETURNS @child_repositories TABLE
                                (
                                    Id           INT,
                                    RepositoryId NVARCHAR(200),
                                    Name         VARCHAR(255),
                                    Description  TEXT,
                                    Visibility   VARCHAR(50),
                                    ParentId     INT,
                                    CreatedBy    INT,
                                    CreatedOn    DATETIME,
                                    ModifiedBy   INT,
                                    ModifiedOn   DATETIME,
                                    DeletedBy    INT,
                                    DeletedOn    DATETIME
                                ) AS
BEGIN
    WITH repository_tree AS (
        -- starting with current repositoy
        SELECT Id,
               RepositoryId,
               Name,
               Description,
               Visibility,
               ParentId,
               CreatedBy,
               CreatedOn,
               ModifiedBy,
               ModifiedOn,
               DeletedBy,
               DeletedOn
        FROM t_Repositories
        WHERE Id = @repository_id

        UNION ALL
        -- find children of current repository, recursively
        SELECT r.Id,
               r.RepositoryId,
               r.Name,
               r.Description,
               r.Visibility,
               r.ParentId,
               r.CreatedBy,
               r.CreatedOn,
               r.ModifiedBy,
               r.ModifiedOn,
               r.DeletedBy,
               r.DeletedOn
        FROM t_Repositories r
                 INNER JOIN repository_tree rt ON r.ParentId = rt.Id)
    -- Insert the results
    INSERT
    INTO @child_repositories
    SELECT *
    FROM repository_tree;

    RETURN;
END;
