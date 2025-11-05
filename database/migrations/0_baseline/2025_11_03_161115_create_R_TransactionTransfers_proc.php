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
        DB::unprepared("CREATE    PROC [dbo].[R_TransactionTransfers] 

@FromDate DATE = NULL,
@ToDate DATE = NULL,
@FromBranch VARCHAR = NULL,
@ToBranch VARCHAR = NULL

AS
BEGIN
    SET NOCOUNT ON;
Create table #StockTransfer (

  --TransferID VARCHAR(20),
  ItemName VARCHAR (50),
  TransferDate DATE,
  FromBranch VARCHAR (20),
  ToBranch VARCHAR (20),
  TransferedBy VARCHAR (50),
  RequisitionType VARCHAR (50)

)

Insert Into #StockTransfer

Select 
  --MT. TransferID,
  I.ItemName AS ItemName,
  MT.TransferDate,
  FB.Name AS FromBranch,
  TB.Name AS ToBranch,
  U.UserID AS TransferedBy, 
  MT.RequisitionType
 
  
from t_Transfers AS MT

INNER JOIN t_TransferItems  TI ON MT.Id  = TI.TransferId 
INNER JOIN t_Items  I ON TI.Item  = I.Id
INNER JOIN t_Branches  FB ON MT.FromBranch = FB.Id 
INNER JOIN t_Branches  TB ON MT.ToBranch = TB.Id
INNER JOIN t_Users  U ON MT.TransferredBy = U.Id

WHERE (@FromDate IS NULL OR MT.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR MT.CreatedOn < DATEADD(DAY, 1, @ToDate));


	  select * from #StockTransfer

END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_TransactionTransfers");
    }
};
