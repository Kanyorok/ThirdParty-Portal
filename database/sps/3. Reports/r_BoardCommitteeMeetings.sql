CREATE or alter PROCEDURE [dbo].[r_BoardCommitteeMeetings](
    @MeetingTitle VARCHAR(1000) = NULL,
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT a.Title,
           a.Type,
           a.Location,
           a.Notes,
           CASE
               WHEN a.StatusID = 'cc' THEN 'Canceled'
               WHEN a.StatusID = 'sc' THEN 'Scheduled'
               WHEN a.StatusID = 'so' THEN 'Ongoing'
               WHEN a.StatusID = 'ss' THEN 'Completed'
               ELSE 'Unknown'
               END AS Status,
           a.StartOn,
           a.EndOn,
           CASE
               WHEN a.Source = 'CommitteeID' THEN 'Committee'
               ELSE NULL
               END AS Source,
           b.Name  AS CommitteeName,
           d.Name  AS BoardMemberName,
           d.Phone,
           d.Role
    FROM t_Meetings a
             LEFT JOIN t_Committees b
        --ON a.SourceID = b.Id
                       ON a.Source = 'CommitteeID'
             LEFT JOIN t_MeetingBoard c
                       ON a.MeetingId = c.MeetingId
             LEFT JOIN t_BoardMembers d
                       ON c.BoardMemberId = d.Id
    WHERE a.Type = 'BoardMemberID'
      AND (@FromDate IS NULL OR a.StartOn >= @FromDate)
      AND (@ToDate IS NULL OR a.StartOn < DATEADD(DAY, 1, @ToDate))
      AND (@MeetingTitle IS NULL OR a.Title = @MeetingTitle);

    SET NOCOUNT OFF;
END
--GO
