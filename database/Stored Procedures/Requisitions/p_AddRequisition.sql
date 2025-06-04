alter PROCEDURE p_AddRequisition
    @Branch nvarchar(2),
    @Department nvarchar(2),
    @Remarks nvarchar(max),
    @Plan varchar(20),
    @User int
AS
BEGIN
    SET NOCOUNT ON

    DECLARE @RequisitionNo varchar(10), @RequisitionId bigint, @StatusID bigint;


    select @StatusID= c.ID from t_CodeDetails c (nolock) where c.CodeID='RequisitionStatus' and c.Description='Pending'

    -- Insert new requisition
    INSERT INTO t_Requisitions (RequisitionNo, BranchId, DepartmentId, Remarks, CreatedBy,CreatedOn, ModifiedBy,ModifiedOn,StatusID,PlanRef)
    VALUES (@RequisitionNo, @Branch, @Department, @Remarks, @User, getdate(),@User,getdate(),@StatusID,@Plan)

    -- Generate RequisitionNo
    SET @RequisitionId = (SELECT MAX (r.Id) FROM t_Requisitions r)

    SET @RequisitionNo = (
        SELECT 'REQ-' + RIGHT(REPLICATE('0', 4) + CAST(isnull(MAX(@RequisitionId),0) AS VARCHAR), 4)

    )


    UPDATE t set t.RequisitionNo = @RequisitionNo from t_Requisitions t where t.id=@RequisitionId

    SET NOCOUNT OFF
END




--EXEC p_AddRequisition 01, 01, 'rr', 'Purchase Requisition', 2




