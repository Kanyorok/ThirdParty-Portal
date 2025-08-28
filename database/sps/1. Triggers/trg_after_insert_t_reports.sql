-- Created @teresa.nyaata on July 29 2025
-- Updated @mureithi.maina on August 29, 2025

CREATE OR ALTER TRIGGER [dbo].[trg_after_insert_t_reports]
    ON [dbo].[t_Reports]
    AFTER INSERT
    AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO t_permissions (name,
                               guard_name,
                               created_at,
                               updated_at,
                               ModuleId)
    SELECT 'report_' + REPLACE(i.name, ' ', '_') AS name,
           'web'                                 AS guard_name,
           GETDATE()                             AS created_at,
           GETDATE()                             AS updated_at,
           i.moduleid
    FROM inserted i;

END;
