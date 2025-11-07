-- SQL Server script to add a surrogate ModelRoleId identity column to t_ModelRoles
-- Run this in SSMS or via sqlcmd. BACKUP your database before running.
-- This script creates a new table, copies data, and swaps names.

BEGIN TRANSACTION;

PRINT 'Creating backup copy of existing table as t_ModelRoles_backup...';
IF OBJECT_ID('dbo.t_ModelRoles_backup') IS NOT NULL
    DROP TABLE dbo.t_ModelRoles_backup;

SELECT * INTO dbo.t_ModelRoles_backup FROM dbo.t_ModelRoles;

PRINT 'Creating new table __t_ModelRoles_new with ModelRoleId identity...';
IF OBJECT_ID('dbo.__t_ModelRoles_new') IS NOT NULL
    DROP TABLE dbo.__t_ModelRoles_new;

CREATE TABLE dbo.__t_ModelRoles_new (
    ModelRoleId INT IDENTITY(1,1) NOT NULL,
    model_id NVARCHAR(255) NULL,
    model_type NVARCHAR(255) NULL,
    role_id INT NULL,
    BranchId INT NULL,
    CreatedOn DATETIME2 NULL,
    ModifiedOn DATETIME2 NULL,
    DeletedOn DATETIME2 NULL
    -- Modify/add columns below to match your current t_ModelRoles schema if different.
);

PRINT 'Copying data to new table...';
INSERT INTO dbo.__t_ModelRoles_new (model_id, model_type, role_id, BranchId, CreatedOn, ModifiedOn, DeletedOn)
SELECT model_id, model_type, role_id, BranchId, CreatedOn, ModifiedOn, DeletedOn FROM dbo.t_ModelRoles;

PRINT 'Renaming tables (original -> old)';
IF OBJECT_ID('dbo.t_ModelRoles_old') IS NOT NULL
    DROP TABLE dbo.t_ModelRoles_old;
EXEC sp_rename 'dbo.t_ModelRoles', 't_ModelRoles_old';

PRINT 'Renaming new table to t_ModelRoles';
EXEC sp_rename 'dbo.__t_ModelRoles_new', 't_ModelRoles';

COMMIT TRANSACTION;

PRINT 'Done. Verify schema and test application. Keep t_ModelRoles_backup and t_ModelRoles_old until satisfied.';
