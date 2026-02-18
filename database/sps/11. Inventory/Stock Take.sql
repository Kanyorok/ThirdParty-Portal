CREATE OR ALTER PROCEDURE [dbo].[p_AddEditCancelStopPayments]
(
	@OurBranchID		     VARCHAR(6),
	@AccountTypeID		     VARCHAR(5),
	@AccountID			     VARCHAR(16),
	@RequestReferenceNo	     VarChar(10)= NULL,
	@StartChequeID		     Int,
	@EndChequeID		     Int,
	@ChequeDate			     SmallDateTime,
	@ChequeAmount		     Decimal(18, 2),
	@CancelReasonID		     VARCHAR(5),
	@CancelReason		     VARCHAR(250),
	@CancelledBy		     VARCHAR(100),
	@CancelledDate		     SmallDateTime,
	@CreatedBy			     VARCHAR(100),
	@CreatedOn			     SmallDateTime = Null,
	@ModifiedBy			     VARCHAR(100) ,
	@ModifiedOn			     SmallDateTime = Null,
	@SupervisedBy		     VARCHAR(100) = Null,
	@NewRecord			     TinyInt
)
 
AS
BEGIN
--return
	SET NOCOUNT ON
	DECLARE
		@StartingPosition	Int,
		@NoOfLeafToUpdate	Int,
		@IssueChequeEnd		Int,
		@IssueChequeStart	Int,
		@Updation			Int,
		@ErrNumber			nVarChar(20),
		@RefNo				VarChar(10),
		@IsBC				Bit,
		@ChequeCount		int,
		@ChequeNumber		Int

		--CREATE TABLE #StopPamynents
		--(
		--	AccountID		Varchar(5),
		--	OurBranchID	Varchar(6),
		--	ChequeID		Int,
		--	ChequePrefix	Varchar(5)
		--)
	SELECT @ChequeCount = @EndChequeID - @StartChequeID + 1

	IF @NewRecord = 1
	BEGIN

		EXEC p_GetNextMasterKey 1385, Null, @OurBranchID, @RequestReferenceNo OUTPUT

		IF @RequestReferenceNo IS NULL
		BEGIN
			RAISERROR(N'BREXDB005314', 16, 1) -- ID Definition Not Exists
			RETURN
		END

		--------------------------------kamunya 29.04.2023-------------------------------------------------------------------------------
		  DECLARE	  @CountryID Varchar(4), @ChequeLength int, @StrtChqPrefixNChqID Varchar(20)
				, @ChequePrefix Varchar(4), @PadChar varchar(15), @EndChqPrefixNChqID Varchar(20)
				, @PrefixLen Int, @CancelStopChequeNarration Varchar(100)
/*
		  SELECT @CountryID = CountryID, @ChequeLength = ChequeIDLength
			FROM t_systemBankSetting

		  IF @CountryID = 'ET'
			BEGIN
				SELECT @PrefixLen = CHARINDEX(':', @CancelReason); 
				SELECT @ChequePrefix = LEFT(@CancelReason,@PrefixLen - 1)
				SELECT @CancelReason = SUBSTRING(@CancelReason, @PrefixLen + 1, LEN(@CancelReason) - @PrefixLen)
				SELECT @PadChar = REPLICATE('0',@ChequeLength)
				SELECT @StrtChqPrefixNChqID =  @ChequePrefix + RIGHT(@PadChar + RTRIM(CAST(@StartChequeID AS VARCHAR(11))), @ChequeLength)
				SELECT @EndChqPrefixNChqID =  @ChequePrefix + RIGHT(@PadChar + RTRIM(CAST(@EndChequeID AS VARCHAR(11))), @ChequeLength)
				SELECT @CancelStopChequeNarration = @StrtChqPrefixNChqID + '-' + @EndChqPrefixNChqID
				
			
			
				
				SELECT @ChequeNumber = @StartChequeID
				WHILE @ChequeCount > 0
				BEGIN

					SET @ErrNumber = (SELECT dbo.f_ChequeCancelValidations(@OurBranchID, @AccountTypeID, @AccountID,
							@ChequeNumber, @ChequeNumber, @ChequeAmount, @ChequePrefix))

					IF @ErrNumber IS NOT NULL
					BEGIN
						RAISERROR(@ErrNumber, 16, 1) 
						RETURN 
					END

					--INSERT INTO #StopPamynents
					--VALUES (@AccountID, @OurBranchID,@ChequeNumber, @ChequePrefix)
					SELECT @ChequeCount = @ChequeCount - 1
					--If CPO was cancelled due to office void, then we should not allow cancel stop payment
					IF EXISTS (SELECT 1
					FROM t_Chequestoppayment(NOLOCK)
					WHERE OurBranchID = @OurBranchID
						AND AccountTypeID = @AccountTypeID
						AND AccountID = @AccountID
						AND @ChequeNumber BETWEEN StartChequeID AND EndChequeID
						AND StopPaymentReasonID IN ('V','VD','VR','PL')) -- AND ChequePrefix = 'CPO'
						BEGIN
							RAISERROR(N'BREXDB740908', 16, 1)
							RETURN
						END
					--------------------------------------------------------------------------------------
					SELECT @ChequeNumber=@ChequeNumber+1
				END
			END
			ELSE
            */
			BEGIN
				SELECT @ChequePrefix = ''
				SELECT @CancelStopChequeNarration = RIGHT('000000'+convert(nvarchar(5),@StartChequeID),6) + SPACE(2) +  '-' 
										+ SPACE(2) +   RIGHT('000000'+'000000'+convert(nvarchar(5),@EndChequeID),6)
			END
	  -----------------------------------------------------------------------------------------------------------------------------------

			INSERT INTO t_CancelStopPayment
			(
				OurBranchID,
				AccountTypeID,
				AccountID,
				RequestReferenceNo,
				StartChequeID,
				EndChequeID,
				ChequeDate	,
				ChequeAmount,
				CancelReasonID,
				CancelReason,
				CancelledBy,
				CancelledDate,
				CreatedBy,
				CreatedOn,
				SupervisedBy,
				SupervisedOn,
				UpdateCount,
				ChequePrefix
			)
			VALUES
			(
				@OurBranchID,
				@AccountTypeID,
				@AccountID,
				@RequestReferenceNo,
				@StartChequeID,
				@EndChequeID,
				@ChequeDate	,
				@ChequeAmount,
				@CancelReasonID,
				@CancelReason,
				@cancelledBy,
				@CancelledDate,
				@CreatedBy,
				IsNull(@CreatedOn, GETDATE()),
				@SupervisedBy,
				CASE WHEN @SupervisedBy IS NOT NULL THEN GETDATE() ELSE NULL END,
				2,
				@ChequePrefix
			)
	END
/*
	ELSE
	BEGIN
		UPDATE	t_CancelStopPayment
		SET		StartChequeID = @StartChequeID,
				EndChequeID = @EndChequeID,
				ChequeDate	= @ChequeDate,
				ChequeAmount = @ChequeAmount,
				CancelReasonID = @CancelReasonID,
				CancelReason = @CancelReason,
				CancelledBy = @CancelledBy,
				CancelledDate = @CancelledDate,
				ModifiedBy = @ModifiedBy,
				ModifiedOn = IsNull(@ModifiedOn,GetDate()),
				SupervisedBy = @SupervisedBy,
				SupervisedOn = CASE WHEN @SupervisedBy IS NOT NULL THEN GETDATE() ELSE NULL END,
				UpdateCount	=dbo.f_GetNextUpdateCount(@NewRecord)
		WHERE	t_CancelStopPayment.OurBranchID = @OurBranchID
				AND t_CancelStopPayment.AccountTypeID = @AccountTypeID
				AND t_CancelStopPayment.AccountID = @AccountID
				AND t_CancelStopPayment.RequestReferenceNo = @RequestReferenceNo
				AND  t_CancelStopPayment.UpdateCount = @NewRecord
		
		
	END
*/	

	SET @Updation = 0
	WHILE @Updation <= (@EndChequeID - @StartChequeID) + 1
	BEGIN
		SELECT	@RefNo = RequestReferenceNo,
				@IssueChequeEnd	= ChequeEnd,
				@IssueChequeStart = ChequeStart
		FROM	t_ChequeBook
		WHERE	OurBranchID = @OurBranchID
				AND AccountTypeID = @AccountTypeID
				AND AccountID = @AccountID
				AND @StartChequeID	BETWEEN ChequeStart AND ChequeEnd

		IF	@IssueChequeEnd < @EndChequeID
			SET @NoOfLeafToUpdate = (@IssueChequeEnd - @StartChequeID) + 1
		ELSE
			SET @NoOfLeafToUpdate = (@EndChequeID - @StartChequeID) + 1

		SET	@StartingPosition = (@StartChequeID - @IssueChequeStart) + 1
		
		UPDATE	t_ChequeBook
		SET		Cheques = STUFF(Cheques, @StartingPosition, @NoOfLeafToUpdate, REPLICATE('U', @NoOfLeafToUpdate))
		WHERE	OurBranchID = @OurBranchID 
				AND AccountTypeID = @AccountTypeID 
				AND AccountID = @AccountID 
				AND RequestReferenceNo = @RefNo
				AND ChequePrefix = IsNull(@ChequePrefix,ChequePrefix)

		IF @IssueChequeEnd < @EndChequeID
		BEGIN
			SET	@StartChequeID = @IssueChequeEnd + 1
		END
		SET	@Updation = @Updation + @NoOfLeafToUpdate
	END
	--	Modify/Remove t_ChequeTrx ChequeStatusID: to (S)topped (GL Accounts) GLCategoryID = 'DREC'
	--	Existing entry in t_ChequeTrx with ChequeStatusID (S)topped: Modify to ''
	--	Existing entry in t_ChequeTrx: Remove entry

	SET		@Updation = @StartChequeID
	WHILE	@Updation <= @EndChequeID
	BEGIN
		If Exists (SELECT AccountID FROM t_GeneralLedger(NOLOCK) Where GLCategoryID = 'DREC' And AccountID = @AccountID)
		Begin
			If Exists (SELECT AccountID FROM t_ChequeTrx(NOLOCK) Where OurBranchID = @OurBranchID
			And AccountID = @AccountID And ChequeID = @Updation And ChequeStatusID = 'S')
			Begin
				Set @IsBC = 1
			End
		End
		Else
		Begin
			Set @IsBC = 0
		End
		If @IsBC = 1
		Begin
			IF @ChequePrefix = 'CPO'
			BEGIN
				IF EXISTS (SELECT 1
							FROM t_ReconcilableItem(NOLOCK)
							WHERE OurBranchID = @OurBranchID
								AND AccountID = @AccountID
								AND ChequeID = @Updation
								AND IsNull(ReconcileStatusID,'') = 'I')
					BEGIN
						UPDATE t_ChequeTrx
						SET ChequeStatusID = ''
						WHERE OurBranchID = @OurBranchID
							AND AccountTypeID = @AccountTypeID
							AND AccountID = @AccountID
							AND ChequeID = @Updation
							AND ChequeStatusID = 'S'
							AND ChequePrefix = IsNull(@ChequePrefix,ChequePrefix)
					END
				ELSE
					BEGIN
						DELETE FROM t_ChequeTrx
						WHERE OurBranchID = @OurBranchID
							AND AccountTypeID = @AccountTypeID
							AND AccountID = @AccountID
							AND ChequeID = @Updation 
							AND ChequeStatusID = 'S'
							AND ChequePrefix = IsNull(@ChequePrefix,ChequePrefix)
					END
			END
			ELSE IF @ChequePrefix IN (SELECT [Description] FROM t_usercodedetail WHERE ID ='ChequePrefix')
			BEGIN
				DELETE FROM t_ChequeTrx
				WHERE OurBranchID = @OurBranchID
					AND AccountTypeID = @AccountTypeID
					AND AccountID = @AccountID
					AND ChequeID = @Updation 
					AND ChequeStatusID = 'S'
					AND ChequePrefix = IsNull(@ChequePrefix,ChequePrefix)
			END
		End 
		Else
		Begin	
			DELETE FROM t_ChequeTrx
			WHERE OurBranchID = @OurBranchID
				AND AccountTypeID = @AccountTypeID
				AND AccountID = @AccountID
				AND ChequeID = @Updation 
				AND ChequeStatusID = 'S'
				AND ChequePrefix = IsNull(@ChequePrefix,ChequePrefix)
		End
		SET @Updation = @Updation + 1	
	END
	SELECT @RequestReferenceNo
	
	SET NOCOUNT OFF
END





