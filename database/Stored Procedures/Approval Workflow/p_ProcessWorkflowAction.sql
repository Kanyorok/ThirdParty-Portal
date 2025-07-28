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
            @StatusID = CASE @ActionType WHEN 'approve' THEN 1 ELSE 2 END -- Assuming 1=Approved, 2=Rejected
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
 
        -- Record action in history (excluding ActionType and StageID)
        INSERT INTO dbo.t_WorkFlowHistory (
            Source, SourceID, Notes, StatusId,
            CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
        )
        VALUES (
            @Source, @SourceID, @Notes, @StatusID,
            @UserID, GETDATE(), @UserID, GETDATE()
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
            DECLARE @LastApproverColumn NVARCHAR(100) = '';
            DECLARE @UpdateSQL NVARCHAR(MAX);
            DECLARE @KeyColumn NVARCHAR(50) = 'Id';
            DECLARE @StatusToSet NVARCHAR(50);
 
            -- Check if LastApprover column exists
            IF EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = PARSENAME(@Source, 1)
                  AND COLUMN_NAME = 'LastApprover'
            )
            BEGIN
                SET @LastApproverColumn = ', LastApprover = @UserName';
            END
 
            -- Find key column name (Id or ID)
            SELECT TOP 1 @KeyColumn = COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = PARSENAME(@Source, 1)
              AND COLUMN_NAME IN ('Id', 'ID');
 
            IF @KeyColumn IS NULL
                SET @KeyColumn = 'Id';
 
            -- Determine status to set
            SET @StatusToSet = CASE WHEN @ActionType = 'approve' THEN @ApprovedStatus ELSE @RejectedStatus END;
 
            -- Build update SQL string
            SET @UpdateSQL = N'
            UPDATE ' + QUOTENAME(@Source) + '
            SET ' + QUOTENAME(@StatusColumn) + ' = @Status,
                ModifiedBy = @UserID,
                ModifiedOn = GETDATE()' + @LastApproverColumn + '
            WHERE ' + QUOTENAME(@KeyColumn) + ' = @SourceID';
 
            -- Execute the dynamic update statement
            EXEC sp_executesql @UpdateSQL,
                N'@Status NVARCHAR(50), @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                @StatusToSet, @UserName, @SourceID, @UserID;
        END
 
        COMMIT TRANSACTION;
 
        SELECT 'SUCCESS' AS Status,
               CASE WHEN @ActionType = 'approve' THEN 'Approval recorded' ELSE 'Rejection recorded' END AS Message,
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
