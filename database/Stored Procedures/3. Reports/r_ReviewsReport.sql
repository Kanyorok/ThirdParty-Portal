create OR ALTER PROCEDURE [dbo].[r_ReviewsReport](
    @Source VARCHAR(1000) = null,
    @FromDate DATETIME = null,
    @ToDate DATETIME = null
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT r.BranchID,
           r.Party,
           r.Source,
           r.Rating,
           r.Content,
           CASE
               WHEN r.Tonality = 'po' THEN 'Positive'
               WHEN r.Tonality = 'ng' THEN 'Negative'
               ELSE 'Neutral'
               END                                                  AS Tonality,
           (SELECT u.Name FROM t_users u WHERE u.Id = r.CreatedBy)  AS CreatedBy,
           r.CreatedOn,
           (SELECT u.Name FROM t_users u WHERE u.Id = r.ModifiedBy) AS ModifiedBy,
           r.ModifiedOn,
           COALESCE(r.Party, c.Name)                                AS PartyName
    FROM t_Reviews r
             LEFT JOIN syn_t_Client c
                       ON r.PartyID = c.ClientID AND r.Party = 'ClientID'
    WHERE r.Source = @Source
      AND r.CreatedOn BETWEEN @FromDate AND @ToDate
    ORDER BY r.CreatedOn ASC;

    SET NOCOUNT OFF;
END;

GO
