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
        DB::unprepared("
CREATE PROC [dbo].[t_SupplierEvaluation]
AS
BEGIN
CREATE TABLE #SupplierEvaluation
	(
		CommitteeMemberName		VARCHAR(300),
		UserCode				VARCHAR(200),
		RFQID					VARCHAR(300),
		RFQComment				VARCHAR(500),
		Confirmation			INT,
		CreatedBy				VARCHAR(200),
		CreatedOn				DATE
	)

	INSERT INTO #SupplierEvaluation 
	SELECT 
		E.CommitteeMemberName,		
		E.UserCode,				
		L.ItemName as RFQID,					
		E.RFQComment,			
		E.Confirmation,		
		U.Name as CreatedBy,				
		E.CreatedOn				

		FROM  t_RFQEvaluations E 
		JOIN t_RFQLines L ON L.RFQId=E.RFQID
		JOIN t_users U ON U.ID=E.CreatedBy

		SELECT * from #SupplierEvaluation;

		END

--select * from t_RFQEvaluations
--select * from t_RFQEvaluation_Evaluation
--select * from t_RFQLines
--select * from t_RFQ
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS t_SupplierEvaluation");
    }
};
