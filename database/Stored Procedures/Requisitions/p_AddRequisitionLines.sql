alter PROCEDURE p_AddRequisitionLines
    @RequisitionId bigint,
    @Item varchar(2),
    @Quantity decimal,
    @NeededBy date,
    @Urgency int,
    @User int
AS
BEGIN
    SET NOCOUNT ON

    DECLARE @Description varchar(max), @Type varchar(10), @UOM varchar(10), @CategoryID int, @ExpectedPrice decimal;;

    SELECT @Description=t.ItemDescription,@Type=t.ItemType, @UOM=t.UOM,@ExpectedPrice=0 FROM t_Items t (NOLOCK )
    LEFT JOIN t_ItemCategories c ON t.Id=c.ParentId  WHERE t.id=@Item

    -- Insert new requisition
    INSERT INTO t_RequisitionLines (RequisitionId, Item, Quantity,  UrgencyID, CreatedBy,CreatedOn, ModifiedBy,ModifiedOn,Type,StatusID,Description,UOM,ExpectedPrice)
    VALUES (@RequisitionId, @Item, @Quantity, @Urgency, @User, getdate(),@User,getdate(),@Type,'p',@Description,@UOM,@ExpectedPrice*@Quantity)

--
--     -- Generate RequisitionNo
--     SET @RequisitionId = (SELECT MAX (r.Id) FROM t_Requisitions r)
--
--     SET @RequisitionNo = (
--         SELECT 'REQ-' + RIGHT(REPLICATE('0', 4) + CAST(isnull(MAX(@RequisitionId),0) AS VARCHAR), 4)
--
--     )
--
--
--     UPDATE t set t.RequisitionNo = @RequisitionNo from t_Requisitions t where t.id=@RequisitionId

    SET NOCOUNT OFF
END

