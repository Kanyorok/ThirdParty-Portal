
CREATE or ALTER     PROCEDURE [dbo].[p_CreateWorkflowLimitWithPermission]
    @WorkFlowStageId         [bigint],
    @MaxAmount       DECIMAL(20, 4),
    @PermissionName  NVARCHAR(250),
    @ModuleID        INT,
    @CreatedBy       BIGINT,
    @WorkflowLimitID BIGINT = NULL OUTPUT,
    @PermissionID    BIGINT = NULL OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRY
        BEGIN TRAN; 

        DECLARE @ExistingPermissionID BIGINT,
                @ExistingLimitID BIGINT;

        -- Validate required inputs
        IF @WorkFlowStageId IS NULL
        BEGIN
            ROLLBACK TRAN;
            RAISERROR('WorkFlowStageId is required.', 16, 1);
            RETURN;
        END

        IF NOT EXISTS (SELECT 1 FROM t_WorkFlowStages WHERE Id = @WorkFlowStageId AND DeletedOn IS NULL)
        BEGIN
            ROLLBACK TRAN;
            RAISERROR('WorkFlowStageId does not exist or has been deleted.', 16, 1);
            RETURN;
        END

        -- Check for duplicate permission name
        SELECT @ExistingPermissionID = id
        FROM t_Permissions 
        WHERE name = @PermissionName 
          AND ModuleId = @ModuleID;

        IF @ExistingPermissionID IS NOT NULL
        BEGIN
            ROLLBACK TRAN;
            SELECT 
                'Error: Permission "' + @PermissionName + '" already exists for this module.' AS Message,
                @ExistingPermissionID AS ExistingPermissionID;
            RETURN;
        END

        -- Check for duplicates in t_WorkFlowLimits table
        SELECT @ExistingLimitID = Id
        FROM t_WorkFlowLimits
        WHERE WorkFlowStageId = @WorkFlowStageId
          AND MaxAmount = @MaxAmount
          AND DeletedOn IS NULL;

        IF @ExistingLimitID IS NOT NULL
        BEGIN
            ROLLBACK TRAN;
            SELECT 
                'Error: Workflow limit with the same Source and MaxAmount already exists.' AS Message,
                @ExistingLimitID AS ExistingLimitID;
            RETURN;
        END

        -- Create new permission
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

        SET @PermissionID = SCOPE_IDENTITY();

        -- Insert into t_WorkFlowLimits table
        INSERT INTO t_WorkFlowLimits
        (
            WorkFlowStageId,
            MaxAmount,
            PermissionId,
            CreatedBy,
            CreatedOn,
            ModifiedBy,
            ModifiedOn,
            DeletedBy,
            DeletedOn
        )
        VALUES
        (
            @WorkFlowStageId,
            @MaxAmount,
            @PermissionID,
            @CreatedBy,
            GETDATE(),
            @CreatedBy,
            GETDATE(),
            NULL,
            NULL
        );

        SET @WorkflowLimitID = SCOPE_IDENTITY();

        COMMIT TRAN; 
        
        -- Success Message
        SELECT 
            'Workflow limit and permission created successfully.' AS Message,
            @WorkflowLimitID AS WorkflowLimitID,
            @PermissionID AS PermissionID,
            @PermissionName AS PermissionName;

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