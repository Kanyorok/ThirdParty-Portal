CREATE OR ALTER PROCEDURE dbo.p_ProcessWorkflowAction
    @ActionType NVARCHAR(20), -- 'approve' or 'reject'
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @ApprovedStatus NVARCHAR(50) = 'Approved',
    @RejectedStatus NVARCHAR(50) = 'Rejected'
    AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @StageID NVARCHAR(200) = NULL,
            @WorkFlowID BIGINT = NULL,
            @CurrentStatus NVARCHAR(50) = NULL,
            @HasPendingApprovals BIT = 0,
            @StatusID BIGINT;

BEGIN TRY
BEGIN TRANSACTION;

        -- Get current workflow info and set status ID based on action
SELECT
    @StageID = p.Stage,
    @WorkFlowID = ws.WorkFlowID,
    @StatusID = CASE @ActionType WHEN 'approve' THEN 1 ELSE 2 END -- Assuming 1=Approved, 2=Rejected in t_CodeDetails
FROM dbo.t_WorkFlowPending p
         JOIN dbo.t_WorkFlowStages ws ON p.Stage = ws.Id
WHERE p.Source = @Source
  AND p.SourceID = @SourceID
  AND p.UserId = @UserID
  AND p.DeletedOn IS NULL;

-- Check if there are any pending approvals for this item
SELECT @HasPendingApprovals = CASE WHEN EXISTS (
    SELECT 1 FROM dbo.t_WorkFlowPending
    WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
) THEN 1 ELSE 0 END;

-- Set current status
SET @CurrentStatus = CASE WHEN @HasPendingApprovals = 1 THEN 'Pending' ELSE 'Completed' END;

        -- Validate action
        IF @StageID IS NULL
BEGIN
ROLLBACK TRANSACTION;
SELECT 'ERROR' AS Status, 'No pending approval found for this user' AS Message;
RETURN;
END

        -- Record action in history (now includes all required columns)
INSERT INTO dbo.t_WorkFlowHistory (
    Source, SourceID, Notes, StatusId,
    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn,
    ActionType, StageID
)
VALUES (
           @Source, @SourceID, @Notes, @StatusID,
           @UserID, GETDATE(), @UserID, GETDATE(),
           @ActionType, @StageID
       );

-- Mark approval as processed
UPDATE dbo.t_WorkFlowPending
SET DeletedBy = @UserID,
    DeletedOn = GETDATE(),
    ModifiedBy = @UserID,
    ModifiedOn = GETDATE()
WHERE Source = @Source AND SourceID = @SourceID AND UserId = @UserID;

-- Update source table if rejected or final approval
IF @ActionType = 'reject' OR @HasPendingApprovals = 0
BEGIN
            DECLARE @UpdateSQL NVARCHAR(MAX) = N'
            UPDATE ' + QUOTENAME(@Source) + '
            SET ' + QUOTENAME(@StatusColumn) + ' = @Status,
                ModifiedBy = @UserID,
                ModifiedOn = GETDATE()' +
                CASE WHEN COL_LENGTH(@Source, 'LastApprover') IS NOT NULL
                     THEN ', LastApprover = @UserName'
                     ELSE '' END + '
            WHERE ' + QUOTENAME(COALESCE(
                (SELECT COL_NAME(OBJECT_ID(@Source), ordinal_position)
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_NAME = PARSENAME(@Source, 1)
                 AND COLUMN_NAME IN ('Id', 'ID')), 'Id')) + ' = @SourceID';

EXEC sp_executesql @UpdateSQL,
                N'@Status NVARCHAR(50), @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                CASE WHEN @ActionType = 'approve' THEN @ApprovedStatus ELSE @RejectedStatus END,
                @UserName, @SourceID, @UserID;
END

COMMIT TRANSACTION;

SELECT 'SUCCESS' AS Status,
       CASE
           WHEN @ActionType = 'approve' THEN 'Approval recorded'
           ELSE 'Rejection recorded'
           END AS Message,
       @CurrentStatus AS WorkflowStatus;
END TRY
BEGIN CATCH
IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

SELECT 'ERROR' AS Status,
       ERROR_MESSAGE() AS Message,
       NULL AS WorkflowStatus;
END CATCH
END
