CREATE OR ALTER PROCEDURE p_ProcessWorkflowAction
    @ActionType VARCHAR(20), -- 'approve' or 'reject'
    @Source VARCHAR(100),
    @SourceID INT,
    @UserID VARCHAR(50),
    @UserName NVARCHAR(100) = NULL,
    @Notes NVARCHAR(500) = NULL,
    @StatusColumn VARCHAR(100) = 'Status',
    @ApprovedStatus VARCHAR(50) = 'Approved',
    @RejectedStatus VARCHAR(50) = 'Rejected'
    AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

BEGIN TRY
BEGIN TRANSACTION;

        DECLARE @StageID INT, @WorkFlowID INT, @CurrentStatus VARCHAR(50);

        -- Get current workflow info
SELECT
    @StageID = p.Stage,
    @WorkFlowID = ws.WorkFlowID,
    @CurrentStatus =
    CASE WHEN EXISTS (
        SELECT 1 FROM t_WorkFlowPending
        WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
    ) THEN 'Pending' ELSE 'Completed' END
FROM t_WorkFlowPending p
         JOIN t_WorkFlowStages ws ON p.Stage = ws.Id
WHERE p.Source = @Source AND p.SourceID = @SourceID AND p.UserId = @UserID
  AND p.DeletedOn IS NULL;

-- Validate action
IF @StageID IS NULL
BEGIN
ROLLBACK TRANSACTION;
SELECT 'ERROR' AS Status, 'No pending approval found for this user' AS Message;
RETURN;
END

        -- Record action in history
INSERT INTO t_WorkFlowHistory (Source, SourceID, Notes, StatusId, CreatedBy, CreatedOn, ActionType, StageID)
VALUES (@Source, @SourceID, @Notes,
        CASE @ActionType WHEN 'approve' THEN 1 ELSE 2 END,
        @UserID, GETDATE(), @ActionType, @StageID);

-- Mark approval as processed
UPDATE t_WorkFlowPending
SET DeletedBy = @UserID,
    DeletedOn = GETDATE(),
    ModifiedBy = @UserID,
    ModifiedOn = GETDATE()
WHERE Source = @Source AND SourceID = @SourceID AND UserId = @UserID;

-- Update source table if rejected or final approval
IF @ActionType = 'reject' OR @CurrentStatus = 'Completed'
BEGIN
            DECLARE @UpdateSQL NVARCHAR(MAX) = N'
            UPDATE ' + QUOTENAME(@Source) + '
            SET ' + QUOTENAME(@StatusColumn) + ' = @Status,
                ModifiedBy = @UserName,
                ModifiedOn = GETDATE(),
                LastApprover = @UserName
            WHERE Id = @SourceID';

EXEC sp_executesql @UpdateSQL,
                N'@Status VARCHAR(50), @UserName NVARCHAR(100), @SourceID INT',
                CASE WHEN @ActionType = 'approve' THEN @ApprovedStatus ELSE @RejectedStatus END,
                @UserName, @SourceID;
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
IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;

SELECT 'ERROR' AS Status,
       ERROR_MESSAGE() AS Message,
       NULL AS WorkflowStatus;
END CATCH
END
