CREATE OR ALTER   PROCEDURE [dbo].[p_AddWorkflowStage2]
    @Order              INT,
    @StageName          NVARCHAR(200),
    @EscalationLimit    INT,
    @WorkflowID         BIGINT,
    @WorkflowTypeID     BIGINT,
    @Count              INT,
    @StatusID           BIGINT = NULL,
    @CreatedBy          BIGINT,
    @ModuleID           INT
AS
BEGIN
    SET NOCOUNT ON;
 
    BEGIN TRY
        BEGIN TRAN; -- Start transaction
 
        DECLARE @NewStageID BIGINT;
        DECLARE @PermissionName NVARCHAR(250) = 'workflowstage_' + REPLACE(@StageName, ' ', '');
        DECLARE @ExistingPermissionID BIGINT;
        DECLARE @ExistingStageID BIGINT;
 
        -- Check if the stage already exists for this workflow and order
        SELECT @ExistingStageID = id
        FROM t_WorkFlowStages
        WHERE WorkflowID = @WorkflowID
          AND [Order] = @Order;
 
        -- Insert stage if it does not exist
        IF @ExistingStageID IS NULL
        BEGIN
            INSERT INTO t_WorkFlowStages
            (
                [Order],
                StageName,
                EscalationLimit,
                WorkflowID,
                WorkflowTypeID,
                PermissionID,
                [Count],
                StatusID,
                CreatedBy,
                CreatedOn,
                ModifiedBy,
                ModifiedOn,
                DeletedBy,
                DeletedOn
            )
            VALUES
            (
                @Order,
                @StageName,
                @EscalationLimit,
                @WorkflowID,
                @WorkflowTypeID,
                NULL,
                @Count,
                @StatusID,
                @CreatedBy,
                GETDATE(),
                @CreatedBy,
                GETDATE(),
                NULL,
                NULL
            );
 
            SET @NewStageID = SCOPE_IDENTITY();
        END
        ELSE
        BEGIN
            SET @NewStageID = @ExistingStageID;
        END
 
        -- Insert permission if it doesn't exist
        -- FIX: Check using guard_name 'web' because of unique index on (name, guard_name)
        IF NOT EXISTS (
            SELECT 1 FROM t_Permissions 
            WHERE name = @PermissionName 
              AND guard_name = 'web'
        )
        BEGIN
            INSERT INTO t_Permissions
            (
                name,
                guard_name,
                created_at,
                updated_at,
                ModuleId
            )
            VALUES
            (
                @PermissionName,
                'web',
                GETDATE(),
                GETDATE(),
                @ModuleID
            );
 
            SET @ExistingPermissionID = SCOPE_IDENTITY();
        END
        ELSE
        BEGIN
            SELECT @ExistingPermissionID = id 
            FROM t_Permissions 
            WHERE name = @PermissionName 
              AND guard_name = 'web';
        END
 
        -- Update the workflow stage with the correct permission
        UPDATE t_WorkFlowStages
        SET PermissionID = @ExistingPermissionID
        WHERE id = @NewStageID;
 
        COMMIT TRAN; -- Commit transaction
 
        -- Return result
        SELECT 
            'Workflow stage added successfully.' AS Message,
            @NewStageID AS NewStageID,
            @ExistingPermissionID AS PermissionID;
 
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRAN;
 
        DECLARE 
            @ErrorMessage NVARCHAR(4000),
            @ErrorSeverity INT,
            @ErrorState INT;
 
        SELECT 
            @ErrorMessage = ERROR_MESSAGE(),
            @ErrorSeverity = ERROR_SEVERITY(),
            @ErrorState = ERROR_STATE();
 
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    END CATCH
END;
