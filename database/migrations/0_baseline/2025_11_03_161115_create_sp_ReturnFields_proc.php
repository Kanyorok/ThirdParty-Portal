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
        DB::unprepared("CREATE PROCEDURE [dbo].[sp_ReturnFields]
(
	@TableName	varchar(100), 
	@OneLine	bit=0
)
 
AS
SET DEADLOCK_PRIORITY LOW
	Declare @FieldStr	varchar(4000),
		@FieldName	Varchar(100)
	IF not Exists (select Name from syscolumns Where ID = object_Id(@TableName))
	BEGIN
		Print 'Invalid Object Name'
		Return
	END
	ELse
	BEGIN
		Declare Fields Cursor For select Name from syscolumns Where ID = object_Id(@TableName) Order By colorder
		set  @FieldStr = ''
		Open Fields
		Fetch Next From Fields INTO @FieldName
		while @@Fetch_Status = 0
		Begin
			Set @FieldStr = @FieldStr + ',' + Case @OneLine When 0 Then '' Else Char(10) End + @FieldName 
			Fetch Next From Fields INTO @FieldName
		End
		Close Fields
		Deallocate Fields
	END
	
	Set @FieldStr = right(@FieldStr,len(@FieldStr)-1) 
	Print @FieldStr


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_ReturnFields");
    }
};
