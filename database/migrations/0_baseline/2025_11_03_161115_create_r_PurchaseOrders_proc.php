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
        DB::unprepared("CREATE    PROC [dbo].[r_PurchaseOrders] @FromDate DATETIME = NULL,
                                              @ToDate DATETIME =NULL
AS
BEGIN

    CREATE TABLE #PurchaseOrders

    (
        OrderNo    VARCHAR(20),
        GrvNo      VARCHAR(20),
      
  AccountID  VARCHAR(200),
        --Description
        OrderDate  DATE,
        OrdTotExcl MONEY,
        OrdTotIncl MONEY,
        CreatedBy  VARCHAR(20)

    )

    INSERT INTO #PurchaseOrders

    SELECT D.OrderNo,
           D.GrvNo,
           S.ThirdPartyName as AccountID,
           D.OrderDate,
           D.OrdTotExcl,
           D.OrdTotIncl,
           U.UserID

    FROM t_Orders D
             JOIN t_Users U ON U.ID = D.CreatedBy
             JOIN t_ThirdParties S ON S.ID = D.AccountID


    SELECT * FROM #PurchaseOrders

END

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_PurchaseOrders");
    }
};
