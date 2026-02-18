
create or ALTER     FUNCTION [dbo].[f_CheckMakerCheckerViolation]
(
    @Source VARCHAR(100),
    @SourceID INT,
    @UserID INT
)
RETURNS @Result TABLE
(
    IsViolation BIT,
    FailureReason NVARCHAR(500)
)
AS
BEGIN
    DECLARE @SubmitterId INT;
    DECLARE @IsViolation BIT = 0;
    DECLARE @FailureReason NVARCHAR(500) = NULL;

    -- 1. Validate Source table exists
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_NAME = @Source
          AND TABLE_TYPE = 'BASE TABLE'
    )
    BEGIN
        INSERT INTO @Result VALUES (1, 'Source table "' + @Source + '" does not exist');
        RETURN;
    END

    -- 2. Validate User exists
    IF NOT EXISTS (
        SELECT 1
        FROM t_users
        WHERE Id = @UserID
          AND DeletedOn IS NULL
    )
    BEGIN
        INSERT INTO @Result VALUES (1, 'User ID ' + CAST(@UserID AS VARCHAR(20)) + ' does not exist');
        RETURN;
    END

    -- 3. Check if workflow history exists
    IF NOT EXISTS (
        SELECT 1
        FROM t_WorkFlowHistory
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND DeletedOn IS NULL
    )
    BEGIN
        INSERT INTO @Result VALUES (1, 'No workflow history found for Source="' + @Source +
                                    '", SourceID=' + CAST(@SourceID AS VARCHAR(20)));
        RETURN;
    END

    -- Core maker-checker logic
    SELECT TOP 1 @SubmitterId = CreatedBy
    FROM t_WorkFlowHistory
    WHERE Source = @Source
      AND SourceID = @SourceID
      AND DeletedOn IS NULL
    ORDER BY CreatedOn ASC;

    IF @SubmitterId = @UserID
    BEGIN
        SET @IsViolation = 1;
        SET @FailureReason = 'Maker-checker violation: User ' + CAST(@UserID AS VARCHAR(20)) +
                             ' cannot approve their own submission';
    END

    INSERT INTO @Result VALUES (@IsViolation, @FailureReason);
    RETURN;
END
