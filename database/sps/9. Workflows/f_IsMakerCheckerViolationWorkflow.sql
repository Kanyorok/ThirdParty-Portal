CREATE OR ALTER FUNCTION dbo.f_IsMakerCheckerViolationWorkflow
(
    @Source VARCHAR(100),
    @SourceID INT,
    @UserID INT
)
RETURNS BIT
AS
BEGIN
    DECLARE @SubmitterId INT;
    DECLARE @Result BIT = 0;

    SELECT TOP 1 @SubmitterId = CreatedBy
    FROM t_WorkFlowHistory
    WHERE Source = @Source
      AND SourceID = @SourceID
      AND DeletedOn IS NULL
    ORDER BY CreatedOn ASC;

    IF @SubmitterId = @UserID
        SET @Result = 1;

    RETURN @Result;
END
