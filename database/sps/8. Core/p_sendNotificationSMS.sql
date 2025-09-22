--created  @mureithi.maina - 03 Sep 2025
CREATE OR ALTER PROCEDURE p_sendNotificationSMS(
    @UserID INT,
    @Message VARCHAR(MAX),
    @SenderId INT,
    @Source VARCHAR(250) = '',
    @SourceID VARCHAR(100) = ''
)
AS
BEGIN
    Declare @Result BIT, @UserPhone VARCHAR(200);
    SET NOCOUNT ON;
    SET @Result = 0; -- Initialize result as FALSE

    BEGIN TRY

        select top 1 @UserPhone = isnull(u.Phone, '')
        from t_Users u
        where Id = @UserID

        IF @UserPhone IS NOT NULL AND LEN(RTRIM(@UserPhone)) > 9
            BEGIN
                DECLARE @Now DATETIME = GETDATE();
                DECLARE @smsID VARCHAR(200);


                set @smsID = 'M0' + cast((select count('*') + 1 from t_SMS) as varchar);

                INSERT INTO t_SMS(SMSId, Phone, Content, Source, SourceID, Party, PartyID,
                                  CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
                VALUES (@smsID,
                        @UserPhone,
                        @Message,
                        @Source,
                        @SourceID,
                        'UserID',
                        @UserID,
                        @SenderId,
                        @Now,
                        @SenderId,
                        @Now);

                SET @Result = 1; -- Set result as TRUE on success
            END
        ELSE
            BEGIN
                SET @Result = 0; -- Set result as FALSE when email is NULL or empty
            END
    END TRY
    BEGIN CATCH
        DECLARE @ErrMsg NVARCHAR(4000), @ErrSev INT, @ErrState INT;
        SELECT @ErrMsg = ERROR_MESSAGE(), @ErrSev = ERROR_SEVERITY(), @ErrState = ERROR_STATE();

        SET @Result = 0; -- Set result as FALSE on error
        RAISERROR (@ErrMsg, @ErrSev, @ErrState);
    END CATCH
END
    exec p_sendNotificationSMS
         @UserID = 2,
         @Message = 'Hello cousins',
         @SenderId = 1


select *
from t_SMS
