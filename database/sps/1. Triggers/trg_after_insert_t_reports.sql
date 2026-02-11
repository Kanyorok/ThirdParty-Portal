-- Created @teresa.nyaata on July 29 2025
-- Updated @mureithi.maina on August 29, 2025
-- Updated @joe.njoroge on December 31, 2025

CREATE OR ALTER TRIGGER [dbo].[trg_after_insert_t_reports]
    ON [dbo].[t_Reports]
    AFTER INSERT, UPDATE, DELETE
    AS
BEGIN
    SET NOCOUNT ON;

    -- 1. HANDLE INSERT
    -- If there are rows in 'inserted' but none in 'deleted', it is a new report.
    IF EXISTS (SELECT 1 FROM inserted) AND NOT EXISTS (SELECT 1 FROM deleted)
        BEGIN
            -- Create the new permission
            INSERT INTO t_permissions (name, guard_name, created_at, updated_at, ModuleId)
            SELECT 'report_' + REPLACE(i.name, ' ', '_'),
                   'web',
                   GETDATE(),
                   GETDATE(),
                   i.moduleid
            FROM inserted i;

            -- Sync the generated name back to the t_Reports table
            UPDATE r
            SET r.permissionname = 'report_' + REPLACE(i.name, ' ', '_')
            FROM t_Reports r
                     INNER JOIN inserted i ON r.id = i.id;
        END

        -- 2. HANDLE UPDATE
        -- If rows exist in both, a record was modified.
    ELSE
        IF EXISTS (SELECT 1 FROM inserted) AND EXISTS (SELECT 1 FROM deleted)
            BEGIN
                -- Update the existing permission name based on the old name
                UPDATE p
                SET p.name       = 'report_' + REPLACE(i.name, ' ', '_'),
                    p.ModuleId   = i.ModuleId,
                    p.updated_at = GETDATE()
                FROM t_permissions p
                         INNER JOIN deleted d ON p.name = 'report_' + REPLACE(d.name, ' ', '_')
                         INNER JOIN inserted i ON d.id = i.id;


                UPDATE r
                SET r.permissionname = 'report_' + REPLACE(i.name, ' ', '_')
                FROM t_Reports r
                         INNER JOIN inserted i ON r.id = i.id;
            END

            -- 3. HANDLE DELETE
            -- If rows exist in 'deleted' but not 'inserted', the report was removed.
        ELSE
            IF EXISTS (SELECT 1 FROM deleted)
                BEGIN
                    -- Remove the corresponding permission
                    DELETE p
                    FROM t_permissions p
                             INNER JOIN deleted d ON p.name = 'report_' + REPLACE(d.name, ' ', '_');
                END

END;

