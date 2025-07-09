create or alter PROCEDURE p_AddRequisitionLines
    @RequisitionId bigint,
    @Item bigint,
    @Quantity float,
    @Urgency int,
    @UOM bigint,
    @ExpectedPrice float = 0,
    @LineItemID bigint = NULL,
    @User bigint
AS
BEGIN
    SET NOCOUNT ON

    DECLARE @Description varchar(max), @Type varchar(10), @StatusID int;

    select @StatusID= c.ID from t_CodeDetails c (nolock) where c.CodeID='RequisitionStatus' and c.Description='Pending'

    SELECT @Description = t.ItemDescription, @Type = t.ItemType
    FROM t_Items t (NOLOCK)
             LEFT JOIN t_ItemCategories c ON t.Id = c.ParentId
    WHERE t.Id = @Item

    -- Insert new requisition
    INSERT INTO t_RequisitionLines (RequisitionId, Item, Quantity, UrgencyID, CreatedBy, CreatedOn, ModifiedBy,
                                    ModifiedOn, Type, StatusID, Description, UOM, ExpectedPrice, PlanLineRef)
    VALUES (@RequisitionId, @Item, @Quantity, @Urgency, @User, getdate(), @User, getdate(), @Type, @StatusID,
            @Description, @UOM, @ExpectedPrice * @Quantity, @LineItemID)


    SET NOCOUNT OFF
END

