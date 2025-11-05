<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("--AUTHOR HEZRON BII
--FILL MODULE ORDERKEY
--Run this to restore order

CREATE   PROCEDURE dbo.SetOrderKey(@ModuleID BIGINT)

AS
BEGIN
    DECLARE @s VARCHAR(50) = CONVERT(VARCHAR(50), @ModuleID);
    DECLARE @len INT = LEN(@s);


    -- If number is short (<=5 digits), treat whole value as the 'main' part
    IF @len <= 5
    BEGIN
        RETURN CONVERT(VARCHAR(50), CAST(@s AS BIGINT));
    END

    DECLARE @mainLen INT = @len - 5; -- variable-length main part
    DECLARE @mainPart VARCHAR(50) = SUBSTRING(@s, 1, @mainLen);
    DECLARE @sub2 CHAR(2)        = SUBSTRING(@s, @mainLen + 1, 2);
    DECLARE @p3   CHAR(1)        = SUBSTRING(@s, @mainLen + 3, 1);
    DECLARE @p4   CHAR(1)        = SUBSTRING(@s, @mainLen + 4, 1);
    DECLARE @p5   CHAR(1)        = SUBSTRING(@s, @mainLen + 5, 1);

    -- Remove leading zeros by casting to BIGINT 
    DECLARE @result VARCHAR(100) = CONVERT(VARCHAR(50), CAST(@mainPart AS BIGINT));

    -- Append submodule if it's not '00'
    IF @sub2 <> '00'
        SET @result = @result + '.' + CONVERT(VARCHAR(10), CAST(@sub2 AS INT));

    -- Append deeper levels if not '0'
    IF @p3 <> '0'
        SET @result = @result + '.' + CONVERT(VARCHAR(10), CAST(@p3 AS INT));
    IF @p4 <> '0'
        SET @result = @result + '.' + CONVERT(VARCHAR(10), CAST(@p4 AS INT));
    IF @p5 <> '0'
        SET @result = @result + '.' + CONVERT(VARCHAR(10), CAST(@p5 AS INT));

    UPDATE t_Modules
    SET orderKey = CAST(
        LEFT(orderKey, CHARINDEX('.', orderKey + '.') - 1) +
        '.' +
        REPLACE(SUBSTRING(orderKey, CHARINDEX('.', orderKey + '.') + 1, LEN(orderKey)), '.', '')
        AS FLOAT
    );
    SELECT @result;
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS SetOrderKey");
    }
};
