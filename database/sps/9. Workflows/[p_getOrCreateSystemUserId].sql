USE [BR_ERP]
GO
/****** Object:  StoredProcedure [dbo].[p_getOrCreateSystemUserId]    Script Date: 31/12/2025 17:10:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER     PROCEDURE [dbo].[p_getOrCreateSystemUserId]

    @SystemUserId BIGINT OUTPUT

AS

BEGIN

    SET NOCOUNT ON;



    SELECT TOP 1 @SystemUserId = Id

    FROM t_Users

    WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    -- If not found, insert system user

    IF @SystemUserId IS NULL

    BEGIN

        INSERT INTO [t_Users] (

            [UserID], [Name], [Email], [Phone], [Linked], [Notes], [Email_Signature],

            [Password], [remember_token], [CreatedBy], [CreatedOn], [ModifiedBy], [ModifiedOn],

            [DeletedBy], [DeletedOn], [ImageId], [ClientID], [ExtensionNo], [EmployeeId]

        )

        VALUES (

            'ERPSYS',

            'SYSTEM',

            'SYSTEM ACCOUNT',

            '0',

            0,

            'SYSTEM ACCOUNT',

            NULL,

            'SYSTEM ACCOUNT',

            NULL,

            NULL,

            GETDATE(),

            NULL,

            GETDATE(),

            NULL,

            NULL,

            NULL,

            NULL,

            NULL,

            NULL

        );

        SET @SystemUserId = SCOPE_IDENTITY();

    END

END;

