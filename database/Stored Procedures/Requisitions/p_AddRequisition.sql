CREATE PROCEDURE p_AddRequisition
    @Branch varchar(2),
    @Department varchar(2),
    @Remarks varchar(max),
    @Category varchar(20),
    @User int
AS
BEGIN
    SET NOCOUNT ON

    DECLARE @RequisitionNo varchar(10), @RequisitionId int;


    -- Insert new requisition
    INSERT INTO t_Requisitions (RequisitionNo, BranchId, DepartmentId, Remarks, Category, CreatedBy,CreatedOn, ModifiedBy,ModifiedOn)
    VALUES (@RequisitionNo, @Branch, @Department, @Remarks, @Category, @User, getdate(),@User,getdate())

    -- Generate RequisitionNo
    SET @RequisitionId = (SELECT MAX (r.Id) FROM t_Requisitions r)

    SET @RequisitionNo = (
        SELECT 'REQ-' + RIGHT(REPLICATE('0', 4) + CAST(isnull(MAX(@RequisitionId),0) AS VARCHAR), 4)

    )


    UPDATE t set t.RequisitionNo = @RequisitionNo from t_Requisitions t where t.id=@RequisitionId

    SET NOCOUNT OFF
END
