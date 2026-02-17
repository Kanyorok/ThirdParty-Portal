CREATE OR ALTER PROCEDURE [dbo].[p_ValidateWorkflowDocumentSignature]
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @DocumentId BIGINT,
    @SignatureID BIGINT,
    @IsDocRequired BIT OUTPUT,
    @ErrorMessage NVARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @SourceIDInt INT = TRY_CAST(@SourceID AS INT);

    -- Initialize output parameters
    SET @IsDocRequired = 0;
    SET @ErrorMessage = NULL;


    -- EARLY FAIL: Signature without Document

    IF @SignatureID IS NOT NULL AND @DocumentId IS NULL
    BEGIN
        SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) +
                           ' was provided but no document was included for signing. A document is required.';
        RETURN;
    END


    -- 1. GET DOCUMENT REQUIREMENT FROM STAGE

    SELECT TOP 1
        @IsDocRequired = ISNULL(a.IsDocRequired, 0)
    FROM t_WorkFlowStages a WITH (NOLOCK)
    JOIN t_WorkFlowPending c WITH (NOLOCK) ON TRY_CAST(c.Stage AS BIGINT) = a.Id
    WHERE c.Source = @Source
        AND c.SourceID = @SourceID
        AND c.UserId = @UserID
        AND c.DeletedOn IS NULL
        AND a.DeletedOn IS NULL
        AND a.DeletedBy IS NULL;

    -- If no stage found, set default
    IF @IsDocRequired IS NULL
    BEGIN
        SET @IsDocRequired = 0;
        RETURN;
    END


    -- 2. VALIDATE BUSINESS RULES


  -- 1: Document required but not provided
    IF @IsDocRequired = 1 AND @DocumentId IS NULL
    BEGIN
        SET @ErrorMessage = 'A reference document is required for this approval stage.';
        RETURN;
    END

  -- 2: Document provided (any scenario) - validate
    IF @DocumentId IS NOT NULL
    BEGIN
        -- Validate document exists and is active
        IF NOT EXISTS (
            SELECT 1
            FROM t_Documents WITH (NOLOCK)
            WHERE Id = @DocumentId
                AND DeletedOn IS NULL
                AND DeletedBy IS NULL
        )
        BEGIN
            SET @ErrorMessage = 'Document ID ' + CAST(@DocumentId AS NVARCHAR(20)) + ' is not valid or has been deleted.';
            RETURN;
        END

      -- 3: Signature is mandatory when document is provided
        IF @SignatureID IS NULL
        BEGIN
            SET @ErrorMessage = 'A signature is required to sign the document.';
            RETURN;
        END

        -- Validate signature exists, is active, and belongs to user
        IF NOT EXISTS (
            SELECT 1
            FROM t_DMSSignatures WITH (NOLOCK)
            WHERE Id = @SignatureID
                AND DeletedOn IS NULL
                AND DeletedBy IS NULL
                AND CreatedBy = @UserID
        )
        BEGIN
            -- Determine specific error for better user feedback
            IF NOT EXISTS (SELECT 1 FROM t_DMSSignatures WITH (NOLOCK) WHERE Id = @SignatureID)
            BEGIN
                SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) + ' was not found.';
            END
            ELSE IF EXISTS (
                SELECT 1
                FROM t_DMSSignatures WITH (NOLOCK)
                WHERE Id = @SignatureID
                    AND (DeletedOn IS NOT NULL OR DeletedBy IS NOT NULL)
            )
            BEGIN
                SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) + ' has been deleted.';
            END
            ELSE
            BEGIN
                SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) + ' does not belong to you.';
            END
            RETURN;
        END


        -- INSERT DOCUMENT SIGNATURE RECORD (AFTER ALL VALIDATIONS PASS)

        -- Only insert if we have BOTH DocumentId AND SignatureID
        DECLARE @Content NVARCHAR(MAX);
        DECLARE @SignatureStatus NVARCHAR(50) = 'p';

        -- Create meaningful content for audit trail
        SET @Content =
            'Workflow Action - Source: ' + ISNULL(@Source, '') +
            ' | SourceID: ' + ISNULL(@SourceID, '') +
            ' | UserID: ' + CAST(@UserID AS NVARCHAR(20)) +
            ' | Timestamp: ' + CONVERT(NVARCHAR(23), GETDATE(), 121);

        -- Check for existing record to prevent duplicates
        IF NOT EXISTS (
            SELECT 1
            FROM t_DocumentSignatures WITH (NOLOCK)
            WHERE DocumentId = @DocumentId
              AND SignatureId = @SignatureID
        )
        BEGIN
            INSERT INTO t_DocumentSignatures
                (DocumentId, SignatureId, Content, CreatedBy, CreatedOn,
                 ModifiedBy, ModifiedOn, Status)
            VALUES
                (@DocumentId, @SignatureID, @Content, @UserID, GETDATE(),
                 @UserID, GETDATE(), @SignatureStatus);
        END


    END



    -- If we reach here, validation passed AND insert was completed (if applicable)
    RETURN 0;
END
GO
