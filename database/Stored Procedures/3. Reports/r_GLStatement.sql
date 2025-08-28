Create OR ALTER Procedure [dbo].[r_GLStatement] @FromDate Date =null,
                                                @ToDate Date = null,
                                                @BranchID nvarchar(200) =null
AS
BEGIN
    CREATE TABLE #r_GLStatement
    (
        GLAccount      nvarchar(200),
        OurBranchID    nvarchar(200),
        TrxDate        Date,
        Debit          Money,
        Credit         Money,
        RunningBalance Money

    )

    Insert into #r_GLStatement
    Select JL.JournalEntryId,
           B.Name as [BranchID],
           JL.CreatedOn,
           JL.Debit,
           JL.Credit,
           JL.Amount
    from t_FinanceJournalLines JL
             JOIN t_branches B ON B.ID = JL.BranchID
--JOIN t_financejournalentries J ON J.ID=JL.JournalEntryId

    Where (@FromDate IS NULL OR JL.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR JL.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @BranchID IS NULL OR
        @BranchID = 'ALL' OR
        B.Name IN (SELECT value FROM STRING_SPLIT(@BranchID, ','))
        );


    select * from #r_GLStatement
END
--GO
--EXEC r_GLStatement
--@FromDate='2025-01-01',
--@ToDate	='2025-08-25',
--@BranchID='Head Office'

GO
