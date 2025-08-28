CREATE OR ALTER PROC [dbo].[r_ProcurementPlanScheduling] @FromDate smalldatetime = null,
                                                         @ToDate smalldatetime = null,
                                                         @SchedulePeriod VARCHAR(100) = null
AS
BEGIN
    CREATE TABLE #ProcurementPlanScheduling
    (
        PlanID         VARCHAR(100),
        ScheduleId     VARCHAR(100),
        PlanLineId     VARCHAR(200),
        SchedulePeriod VARCHAR(100),
        ScheduleQTY    INT,
        CreatedBy      VARCHAR(200),

        CreatedOn      DATE,
    )

    INSERT INTO #ProcurementPlanScheduling
    SELECT C.Title    as PlanId,
           S.ScheduleId,
           I.ItemName as PlanLineId,
           P.SchedulePeriod,
           S.ScheduleQTY,
           U.Name     as CreatedBy,
           S.CreatedOn

    FROM t_SchedulePlan S
             JOIN t_users U ON U.ID = S.CreatedBy
             JOIN t_ConsolidatedProcurementPlan C ON C.PlanID = S.PlanID
             JOIN t_PLanLineItem P ON P.LineItemID = S.PlanLineId
             JOIN t_items I ON I.ID = P.ItemID

    WHERE (@FromDate IS NULL OR S.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR S.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (@SchedulePeriod IS NULL OR @SchedulePeriod = P.SchedulePeriod)


    SELECT * FROM #ProcurementPlanScheduling;

END

GO
