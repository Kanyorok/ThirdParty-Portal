CREATE OR ALTER PROCEDURE [dbo].[r_GLStatement]
(
    @FromDate   DATE = NULL,
    @ToDate     DATE = NULL,
    @BranchID   NVARCHAR(200) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT 1 AS GLStatementPlaceholder;
END
GO
