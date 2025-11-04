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
        DB::unprepared("--select * from INFORMATION_SCHEMA.TABLES where TABLE_NAME like '%doc%'

--select * from t_DocumentLegalHolds
--select * from t_Repositories

CREATE   PROC r_DocumentLegalHold(
@FROM_DATE DATETIME,
@TO_DATE DATETIME
)
AS
SET NOCOUNT ON
BEGIN
CREATE TABLE #DLH(
 Id INT,
 DocumentName NVARCHAR(155),
 MimeType NVARCHAR(100),
 RepositoryName NVARCHAR(100),
 CreatedOn DATETIME,
 CreatedBy NVARCHAR(155),
 ModifiedOn DATETIME,
 ModifiedBy NVARCHAR(155)
 
)
INSERT INTO #DLH(
Id,
DocumentName,
MimeType,
RepositoryName,
CreatedOn,
CreatedBy,
ModifiedOn,
ModifiedBy
)

SELECT 
	lh.Id,
	d.Name,
	d.MimeType,
	r.Name,
	lh.CreatedOn,
	uc.Name,
	lh.ModifiedOn,
	um.Name
	FROM t_DocumentLegalHolds(NOLOCK) lh
	LEFT JOIN t_Documents(NOLOCK) d ON d.Id=lh.DocId
	LEFT JOIN t_Users(NOLOCK) uc ON uc.Id = lh.CreatedBy
	LEFT JOIN t_Users(NOLOCK) um ON um.ID = lh.ModifiedBy
	LEFT JOIN t_Repositories(NOLOCK) r ON r.Id=d.RepositoryId

	WHERE lh.CreatedOn BETWEEN @FROM_DATE AND @TO_DATE
	AND lh.DeletedOn IS NULL
	AND lh.DeletedBy IS NULL

	SELECT * FROM #DLH


END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_DocumentLegalHold");
    }
};
