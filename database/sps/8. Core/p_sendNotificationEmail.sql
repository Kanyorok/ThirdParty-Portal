--created  @mureithi.maina - 03 Sep 2025
CREATE OR ALTER PROCEDURE p_sendNotificationEmail(
    @UserID INT,
    @Subject VARCHAR(MAX),
    @Message VARCHAR(MAX),
    @SenderId INT,
    @Source VARCHAR(250) = '',
    @SourceID VARCHAR(100) = ''
)
AS
BEGIN
    Declare @Result BIT, @UserEmail VARCHAR(250), @UserName VARCHAR(200);
    SET NOCOUNT ON;
    SET @Result = 0; -- Initialize result as FALSE

    BEGIN TRY

        select top 1 @UserEmail = isnull(u.Email, ''),
                     @UserName = isnull(u.Name, '')
        from t_Users u
        where Id = @UserID
        IF coalesce(@UserName, @UserEmail) IS NOT NULL AND LEN(RTRIM(@UserEmail)) > 9
            BEGIN
                DECLARE @Now DATETIME = GETDATE();
                DECLARE @JsonEmail NVARCHAR(MAX);

                -- Format email as JSON array with UserName as key and email as value
                SET @JsonEmail = '[{"' + ISNULL(@UserName, 'User Default') + '":"' + @UserEmail + '"}]';

                INSERT INTO t_Emails ([To], Subject, Body, Text, Source, SourceID, Party, PartyID,
                                      CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
                VALUES (@JsonEmail,
                        @Subject,
                        @Message,
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

/*exec p_sendNotificationEmail
 @UserID    = 0,
 @Subject    = 'New Gafment',
 @Message    = 'Hello cousins',
 @SenderId   = 1*/


