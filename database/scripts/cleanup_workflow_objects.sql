/*
=====================================================================
  BRERP — Workflow Objects Cleanup Script
  
  Purpose: Drop redundant test/copy stored procedures, functions,
           and test tables that were created during workflow
           development and are no longer needed.

  Safe to run in any environment (DEV, UAT, PROD).
  Uses IF EXISTS — will not error if objects are already gone.

  Date:    2026-02-18
  Author:  BRERP Team
=====================================================================
*/

PRINT '=========================================='
PRINT '  BRERP Workflow Cleanup — Starting...'
PRINT '=========================================='
PRINT ''

-- ============================================================
-- SECTION 1: Drop test/copy Stored Procedures
-- ============================================================

PRINT '--- Dropping test Stored Procedures ---'

IF OBJECT_ID('dbo.p_ProcessWorkflowActionTest', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowActionTest;
    PRINT '  Dropped: p_ProcessWorkflowActionTest'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowActionTest1', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowActionTest1;
    PRINT '  Dropped: p_ProcessWorkflowActionTest1'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowPendingTest1', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowPendingTest1;
    PRINT '  Dropped: p_ProcessWorkflowPendingTest1'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowPendingTest2', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowPendingTest2;
    PRINT '  Dropped: p_ProcessWorkflowPendingTest2'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowStagesTest1', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowStagesTest1;
    PRINT '  Dropped: p_ProcessWorkflowStagesTest1'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowStagesTest2', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowStagesTest2;
    PRINT '  Dropped: p_ProcessWorkflowStagesTest2'
END

IF OBJECT_ID('dbo.p_ProcessWorkflowStagesTest3', 'P') IS NOT NULL
BEGIN
    DROP PROCEDURE dbo.p_ProcessWorkflowStagesTest3;
    PRINT '  Dropped: p_ProcessWorkflowStagesTest3'
END

PRINT ''

-- ============================================================
-- SECTION 2: Drop old/replaced scalar function
-- The old f_IsMakerCheckerViolationWorkflow was replaced by
-- the table-valued f_CheckMakerCheckerViolation
-- ============================================================

PRINT '--- Dropping old Functions ---'

IF OBJECT_ID('dbo.f_IsMakerCheckerViolationWorkflow', 'FN') IS NOT NULL
BEGIN
    DROP FUNCTION dbo.f_IsMakerCheckerViolationWorkflow;
    PRINT '  Dropped: f_IsMakerCheckerViolationWorkflow (replaced by f_CheckMakerCheckerViolation)'
END

PRINT ''

-- ============================================================
-- SECTION 3: Drop test Tables
-- Must drop FKs first, then the tables.
-- Order matters: drop child references before parent tables.
-- ============================================================

PRINT '--- Dropping test Tables ---'

-- 3a. t_WorkFlowHistoryTest
IF OBJECT_ID('dbo.t_WorkFlowHistoryTest', 'U') IS NOT NULL
BEGIN
    -- Drop FKs
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowHistoryTest_createdby_foreign')
        ALTER TABLE dbo.t_WorkFlowHistoryTest DROP CONSTRAINT t_WorkFlowHistoryTest_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowHistoryTest_deletedby_foreign')
        ALTER TABLE dbo.t_WorkFlowHistoryTest DROP CONSTRAINT t_WorkFlowHistoryTest_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowHistoryTest_modifiedby_foreign')
        ALTER TABLE dbo.t_WorkFlowHistoryTest DROP CONSTRAINT t_WorkFlowHistoryTest_modifiedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowHistoryTest_statusid_foreign')
        ALTER TABLE dbo.t_WorkFlowHistoryTest DROP CONSTRAINT t_WorkFlowHistoryTest_statusid_foreign;

    DROP TABLE dbo.t_WorkFlowHistoryTest;
    PRINT '  Dropped: t_WorkFlowHistoryTest'
END

-- 3b. t_WorkFlowLimitsTest (child of t_WorkFlowStagesTest)
IF OBJECT_ID('dbo.t_WorkFlowLimitsTest', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowLimitsTest_createdby_foreign')
        ALTER TABLE dbo.t_WorkFlowLimitsTest DROP CONSTRAINT t_WorkFlowLimitsTest_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowLimitsTest_deletedby_foreign')
        ALTER TABLE dbo.t_WorkFlowLimitsTest DROP CONSTRAINT t_WorkFlowLimitsTest_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowLimitsTest_modifiedby_foreign')
        ALTER TABLE dbo.t_WorkFlowLimitsTest DROP CONSTRAINT t_WorkFlowLimitsTest_modifiedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowLimitsTest_permissionid_foreign')
        ALTER TABLE dbo.t_WorkFlowLimitsTest DROP CONSTRAINT t_WorkFlowLimitsTest_permissionid_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowLimitsTest_workflowstageid_foreign')
        ALTER TABLE dbo.t_WorkFlowLimitsTest DROP CONSTRAINT t_WorkFlowLimitsTest_workflowstageid_foreign;

    DROP TABLE dbo.t_WorkFlowLimitsTest;
    PRINT '  Dropped: t_WorkFlowLimitsTest'
END

-- 3c. t_WorkFlowPendingTest
IF OBJECT_ID('dbo.t_WorkFlowPendingTest', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowPendingTest_createdby_foreign')
        ALTER TABLE dbo.t_WorkFlowPendingTest DROP CONSTRAINT t_WorkFlowPendingTest_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowPendingTest_deletedby_foreign')
        ALTER TABLE dbo.t_WorkFlowPendingTest DROP CONSTRAINT t_WorkFlowPendingTest_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowPendingTest_modifiedby_foreign')
        ALTER TABLE dbo.t_WorkFlowPendingTest DROP CONSTRAINT t_WorkFlowPendingTest_modifiedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkFlowPendingTest_userid_foreign')
        ALTER TABLE dbo.t_WorkFlowPendingTest DROP CONSTRAINT t_WorkFlowPendingTest_userid_foreign;

    DROP TABLE dbo.t_WorkFlowPendingTest;
    PRINT '  Dropped: t_WorkFlowPendingTest'
END

-- 3d. t_WorkFlowStagesTest (has FKs from LimitsTest — already dropped above)
IF OBJECT_ID('dbo.t_WorkFlowStagesTest', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_CreatedBy')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_CreatedBy;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_DeletedBy')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_DeletedBy;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_ModifiedBy')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_ModifiedBy;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_PermissionId')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_PermissionId;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_StatusId')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_StatusId;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_WorkFlowId')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_WorkFlowId;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_t_WorkFlowStagesTest_WorkFlowTypeId')
        ALTER TABLE dbo.t_WorkFlowStagesTest DROP CONSTRAINT FK_t_WorkFlowStagesTest_WorkFlowTypeId;

    DROP TABLE dbo.t_WorkFlowStagesTest;
    PRINT '  Dropped: t_WorkFlowStagesTest'
END

-- 3e. t_WorkflowsTest
IF OBJECT_ID('dbo.t_WorkflowsTest', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkflowsTest_createdby_foreign')
        ALTER TABLE dbo.t_WorkflowsTest DROP CONSTRAINT t_WorkflowsTest_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkflowsTest_deletedby_foreign')
        ALTER TABLE dbo.t_WorkflowsTest DROP CONSTRAINT t_WorkflowsTest_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_WorkflowsTest_modifiedby_foreign')
        ALTER TABLE dbo.t_WorkflowsTest DROP CONSTRAINT t_WorkflowsTest_modifiedby_foreign;

    DROP TABLE dbo.t_WorkflowsTest;
    PRINT '  Dropped: t_WorkflowsTest'
END

-- 3f. t_PendingWorkflows_static
IF OBJECT_ID('dbo.t_PendingWorkflows_static', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_pendingworkflows_static_createdby_foreign')
        ALTER TABLE dbo.t_PendingWorkflows_static DROP CONSTRAINT t_pendingworkflows_static_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_pendingworkflows_static_deletedby_foreign')
        ALTER TABLE dbo.t_PendingWorkflows_static DROP CONSTRAINT t_pendingworkflows_static_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_pendingworkflows_static_modifiedby_foreign')
        ALTER TABLE dbo.t_PendingWorkflows_static DROP CONSTRAINT t_pendingworkflows_static_modifiedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_pendingworkflows_static_userid_foreign')
        ALTER TABLE dbo.t_PendingWorkflows_static DROP CONSTRAINT t_pendingworkflows_static_userid_foreign;

    DROP TABLE dbo.t_PendingWorkflows_static;
    PRINT '  Dropped: t_PendingWorkflows_static'
END

-- 3g. t_Workflows_static
IF OBJECT_ID('dbo.t_Workflows_static', 'U') IS NOT NULL
BEGIN
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_workflows_static_createdby_foreign')
        ALTER TABLE dbo.t_Workflows_static DROP CONSTRAINT t_workflows_static_createdby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_workflows_static_deletedby_foreign')
        ALTER TABLE dbo.t_Workflows_static DROP CONSTRAINT t_workflows_static_deletedby_foreign;
    IF EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 't_workflows_static_modifiedby_foreign')
        ALTER TABLE dbo.t_Workflows_static DROP CONSTRAINT t_workflows_static_modifiedby_foreign;

    DROP TABLE dbo.t_Workflows_static;
    PRINT '  Dropped: t_Workflows_static'
END

PRINT ''

-- ============================================================
-- SECTION 4: Verification — list remaining workflow objects
-- ============================================================

PRINT '=========================================='
PRINT '  Cleanup Complete. Remaining objects:'
PRINT '=========================================='

SELECT name, type_desc
FROM sys.objects
WHERE is_ms_shipped = 0
  AND (name LIKE '%Workflow%' OR name LIKE '%workflow%' OR name LIKE '%MakerChecker%')
  AND type IN ('P', 'FN', 'IF', 'TF', 'V', 'U')  -- SPs, Functions, Views, Tables only
ORDER BY type_desc, name;
GO
