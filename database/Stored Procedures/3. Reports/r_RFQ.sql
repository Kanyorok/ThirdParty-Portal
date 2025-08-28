CREATE OR ALTER PROCEDURE [dbo].[r_RFQ] @FromDate smalldatetime = null,
                                        @ToDate smalldatetime= null,
                                        @Status Varchar(25) = null
AS
BEGIN

    Create table #RFQ
    (

        RFQNumber          varchar(100),
        Comments           varchar(150),
        Status             varchar(25),
        SubmissionDeadline date,
        CreatedBy          varchar(50),
        CreatedOn          date
        --ModifiedBy			VARCHAR(50)

    )
    -- create table to temporarily hold Status filter
    DECLARE @StatusTable TABLE
                         (
                             Status VARCHAR(25)
                         );

    IF @Status IS NOT NULL
        BEGIN
            INSERT INTO @StatusTable (Status)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@Status, ',');
        END


    Insert Into #RFQ
    select R.RFQNumber,
           R.Comments,
           R.Status,
           R.SubmissionDeadline,
           U.Name as CreatedBy,
           R.CreatedOn
    --ModifiedBy

    from t_RFQ R
             JOIN t_Users U ON U.Id = R.CreatedBy

    WHERE (@FromDate IS NULL OR R.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR R.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (
        @Status IS NULL
            OR EXISTS (SELECT 1
                       FROM @StatusTable S
                       WHERE S.Status = R.Status)
        );


    select * from #RFQ

END
--go
--exec r_RFQ
--	@FromDate ='1 jan 2025',
--	@ToDate ='22 aug 2025',
--	@Status ='approved,pending'


--select * from t_RFQ
GO
