CREATE OR ALTER PROCEDURE [dbo].[r_SurveyReport](
    @SurveyName VARCHAR(1000) = NULL,
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT s.SurveyID,
           s.Label,
           s.StartOn,

           s.EndOn,
           case
               when s.Status = 'ap' then 'Approval'
               when s.Status = 'dr' then 'Draft'
               when s.Status = 'qu' then 'Queue'
               when s.Status = 'cc' then 'Complete'
               when s.Status = 'ac' then 'Active' end as Status,
           q.SurveyQuestionId,

           case
               when q.Type = 'op' then 'Open'
               when q.Type = 'cl' then 'Closed' end   as Type,
           q.Question,
           q.Notes                                    AS QuestionNotes,

           a.Answer,
           a.Notes                                    AS AnswerNotes,

           r.Party,
           r.PartyID,
           r.Source,

           r.SourceID,

           s.CreatedBy,
           s.CreatedOn,
           s.ModifiedBy,
           s.ModifiedOn

    FROM t_Surveys s
             LEFT JOIN t_SurveyQuestions q
                       ON s.Id = q.SurveyId
             LEFT JOIN t_SurveyQuestionAnswers a
                       ON q.Id = CAST(a.SurveyQuestionID AS BIGINT)
             LEFT JOIN t_SurveyQuestionResponses r
                       ON a.Id = r.SurveyQuestionAnswerID

    WHERE (@SurveyName IS NULL OR s.Id = @SurveyName)
      AND (@FromDate IS NULL OR s.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR s.CreatedOn < DATEADD(DAY, 1, @ToDate))

    ORDER BY q.SurveyQuestionId,
             s.CreatedOn ASC;

    SET NOCOUNT OFF;
END;

GO
