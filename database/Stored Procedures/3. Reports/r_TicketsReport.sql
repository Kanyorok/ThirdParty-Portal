CREATE OR ALTER PROCEDURE [dbo].[r_TicketsReport](
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL,
    -- @Status       VARCHAR(50) = NULL,
    @CategoryID VARCHAR(50) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT t.TicketID,
           t.Notes,
           tc.Description                                           as CategoryID,
           (SELECT f.Name FROM t_users f WHERE f.id = t.PartyID)    AS Party,
           t.Source,
           CASE
               WHEN t.Status = 'A' THEN 'Active'
               WHEN t.Status = 'C' THEN 'Cancelled'
               WHEN t.Status = 'R' THEN 'Resolved'
               WHEN t.Status = 'P' THEN 'Approval'
               END                                                  AS Status,
           t.Priority,
           (SELECT f.Name FROM t_users f WHERE f.id = t.OwnerID)    AS Owner,
           t.StartDate,
           t.EndDate,
           t.ClosedOn,
           (SELECT f.Name FROM t_users f WHERE f.id = t.CreatedBy)  AS CreatedBy,
           t.CreatedOn,
           (SELECT f.Name FROM t_users f WHERE f.id = t.ModifiedBy) AS ModifiedBy,
           t.ModifiedOn
    FROM t_Tickets t
             JOIN t_CodeDetails tc on t.CategoryID = tc.ID and tc.CodeID = 'TicketCategories'
    WHERE (@FromDate IS NULL OR t.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR t.CreatedOn < DATEADD(DAY, 1, @ToDate))
      --AND (@Status IS NULL OR t.Status = @Status)
      AND (@CategoryID IS NULL OR t.CategoryID = @CategoryID)
    ORDER BY t.CreatedOn DESC;

    SET NOCOUNT OFF;
END;

---select * from t_CodeDetails
--select tc.value,tc.Description from  t_CodeDetails tc where tc.CodeID='TicketCategories'
GO
