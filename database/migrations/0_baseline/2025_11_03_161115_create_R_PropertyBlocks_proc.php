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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_PropertyBlocks] @FromDate SMALLDATETIME=NULL,
                                                   @ToDate SMALLDATETIME=NULL
AS

BEGIN

    SET NOCOUNT ON

    CREATE TABLE #PropertyBlocks
    (
        PropertyCode Varchar(200),
        PropertyName Varchar(200),

        BlockName    Varchar(200),
        Description  Varchar(200),
        CreatedBy    Varchar(200),
        CreatedOn    Date,

    )

    INsert Into #PropertyBlocks
    (PropertyCode,
     PropertyName,
     BlockName,
     Description,
     CreatedBy,
     CreatedOn)
    Select PR.Propertycode,
           PR.PropertyName,
           PB.BlockName,
           PB.Description,
           U.Name as CreatedBy,
           PB.CreatedOn
    FROM t_PropertyBlock PB
             JOIN t_PropertyRegistry PR ON PR.ID = PB.ID
             JOIN t_Users U ON PR.ID = U.ID
    WHERE (@FromDate IS NULL OR PB.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR PB.CreatedOn < DATEADD(DAY, 1, @ToDate))
    ORDER by PropertyID
    SELECT * FROM #PropertyBlocks (Nolock)
    SET NOCOUNT OFF
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_PropertyBlocks");
    }
};
