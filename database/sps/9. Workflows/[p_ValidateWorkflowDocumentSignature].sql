USE [BR_ERP]
GO
/****** Object:  StoredProcedure [dbo].[p_ValidateWorkflowDocumentSignature]    Script Date: 13/01/2026 14:05:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

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

    -- 1. GET DOCUMENT REQUIREMENT FROM STAGE
    SELECT TOP 1
        @IsDocRequired = ISNULL(a.IsDocRequired, 0)
    FROM t_WorkFlowStagesTest a
    JOIN t_WorkFlowPendingTest c ON TRY_CAST(c.Stage AS BIGINT) = a.Id
    WHERE c.Source = @Source
        AND c.SourceID = @SourceID
        AND c.UserId = @UserID
        AND c.DeletedOn IS NULL
        AND a.DeletedOn IS NULL
        AND a.DeletedBy IS NULL;

    -- If no stage found, set default and continue (main SP will handle this)
    IF @IsDocRequired IS NULL
    BEGIN
        SET @IsDocRequired = 0;
        -- Don't error here - let main SP handle "no pending approval" error
        RETURN;
    END

    -- 2. VALIDATE BUSINESS RULES

    -- Rule 1: Document required but not provided
    IF @IsDocRequired = 1 AND @DocumentId IS NULL
    BEGIN
        SET @ErrorMessage = 'A reference document is required for this approval stage.';
        RETURN;
    END

    -- Rule 2: Document provided (any scenario) - validate
    IF @DocumentId IS NOT NULL
    BEGIN
        -- Validate document exists and is active
        IF NOT EXISTS (
            SELECT 1
            FROM t_Documents
            WHERE Id = @DocumentId
                AND DeletedOn IS NULL
                AND DeletedBy IS NULL
        )
        BEGIN
            SET @ErrorMessage = 'Document ID ' + CAST(@DocumentId AS NVARCHAR(20)) + ' is not valid or has been deleted.';
            RETURN;
        END

        -- Rule 3: Signature is mandatory when document is provided
        IF @SignatureID IS NULL
        BEGIN
            SET @ErrorMessage = 'A signature is required to sign the document.';
            RETURN;
        END

        -- Validate signature exists, is active, and belongs to user
        IF NOT EXISTS (
            SELECT 1
            FROM t_DMSSignatures
            WHERE Id = @SignatureID
                AND DeletedOn IS NULL
                AND DeletedBy IS NULL
                AND CreatedBy = @UserID
        )
        BEGIN
            -- Determine specific error for better user feedback
            IF NOT EXISTS (SELECT 1 FROM t_DMSSignatures WHERE Id = @SignatureID)
            BEGIN
                SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) + ' was not found.';
            END
            ELSE IF EXISTS (
                SELECT 1
                FROM t_DMSSignatures
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
    END

    -- Rule 4: Signature provided without document
    ELSE IF @SignatureID IS NOT NULL
    BEGIN
        SET @ErrorMessage = 'Signature ID ' + CAST(@SignatureID AS NVARCHAR(20)) + ' was provided but no document was included for signing.';
        RETURN;
    END

    -- If we reach here, validation passed
    RETURN 0;
END
GO
