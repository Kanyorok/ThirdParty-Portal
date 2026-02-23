ALTER PROCEDURE p_AddWorkflowlimit
    @MaxAmount              INT,
    @Source                 NVARCHAR(200),
    @WorkflowStageID        BIGINT,
    @AmountReference        NVARCHAR(200),
    @CreatedBy              BIGINT
    --@ModuleID               VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        BEGIN TRAN;

        DECLARE 
            @NewLimit BIGINT,
            @PermissionName NVARCHAR(250),
            @PermissionID BIGINT;

        -- Build permission name
        SET @PermissionName = 'workflowLimit_' + CAST(@MaxAmount AS NVARCHAR(50));


        -- Prevent duplicate permissions
        IF EXISTS (
            SELECT 1 
            FROM t_Permissions 
            WHERE name = @PermissionName
              AND ModuleId = '300000'
        )
        BEGIN
            THROW 50001, 'Permission already exists for this workflow limit.', 1;
        END

        --create permission
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
            '300000'
        );

        SET @PermissionID = SCOPE_IDENTITY();

       --insert into t_WorkFlowLimitsTest
        INSERT INTO t_WorkFlowLimitsTest
        (
            MaxAmount,
            PermissionId,
            [Source],
            WorkFlowStageId,
            AmountReference,
            CreatedBy,
            CreatedOn,
            ModifiedBy,
            ModifiedOn,
            DeletedBy,
            DeletedOn
        )
        VALUES
        (
            @MaxAmount,
            @PermissionID,
            @Source,
            @WorkflowStageID,
            @AmountReference,
            @CreatedBy,
            GETDATE(),
            @CreatedBy,
            GETDATE(),
            NULL,
            NULL
        );

        SET @NewLimit = SCOPE_IDENTITY();

        COMMIT TRAN;

        SELECT 
            'Workflow Limit and Permission created successfully.' AS Message,
            @NewLimit AS WorkflowLimitID,
            @PermissionID AS PermissionID,
            @PermissionName AS PermissionName;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRAN;

        THROW;
    END CATCH
END;
GO
--exec p_AddWorkflowlimit
--    @MaxAmount  ='7500000',
--    @Source      ='t_FinanceInvoices',
--    @WorkflowStageID     ='1',
--    @AmountReference     ='TotalAmount',
--	@CreatedBy   = '2'
	--@ModuleID   = '300000'