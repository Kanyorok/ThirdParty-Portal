CREATE PROCEDURE GetBudgetWorkspace
    @budgetId INT,
    @branchId INT
AS
BEGIN
    SET NOCOUNT ON;

    -- Step 1: Create temp table
CREATE TABLE #TempBudgetWorkspace (
                                      BudgetID INT,
                                      BranchID INT,
                                      GLCode NVARCHAR(50),
                                      Description NVARCHAR(255),
                                      Month1 DECIMAL(18, 2),
                                      Month2 DECIMAL(18, 2),
                                      Month3 DECIMAL(18, 2),
                                      Month4 DECIMAL(18, 2),
                                      Month5 DECIMAL(18, 2),
                                      Month6 DECIMAL(18, 2),
                                      Month7 DECIMAL(18, 2),
                                      Month8 DECIMAL(18, 2),
                                      Month9 DECIMAL(18, 2),
                                      Month10 DECIMAL(18, 2),
                                      Month11 DECIMAL(18, 2),
                                      Month12 DECIMAL(18, 2),
                                      Totals DECIMAL(18, 2),
                                      Actuals DECIMAL(18, 2),
                                      PercentageChange DECIMAL(18, 2)
);

-- Step 2: Populate the temp table from BudgetGLMasterAllocations
INSERT INTO #TempBudgetWorkspace (
    BudgetID, BranchID, GLCode, Description,
    Month1, Month2, Month3, Month4, Month5, Month6,
    Month7, Month8, Month9, Month10, Month11, Month12,
    Totals, Actuals, PercentageChange
)
SELECT
    BudgetID, BranchID, GLAttachmentID, Description,
    Month1, Month2, Month3, Month4, Month5, Month6,
    Month7, Month8, Month9, Month10, Month11, Month12,
    ISNULL(Month1,0) + ISNULL(Month2,0) + ISNULL(Month3,0) + ISNULL(Month4,0) + ISNULL(Month5,0) + ISNULL(Month6,0) +
    ISNULL(Month7,0) + ISNULL(Month8,0) + ISNULL(Month9,0) + ISNULL(Month10,0) + ISNULL(Month11,0) + ISNULL(Month12,0) AS Totals,
    0 AS Actuals,  -- Placeholder
    0 AS PercentageChange  -- Placeholder
FROM t_BudgetGLMasterAllocations
WHERE BudgetID = @budgetId AND BranchID = @branchId;

-- Step 3: Update Actuals & PercentageChange


-- Step 4: Return the result
SELECT * FROM #TempBudgetWorkspace;
END
