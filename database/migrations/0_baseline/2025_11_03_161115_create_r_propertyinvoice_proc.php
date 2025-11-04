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
        DB::unprepared("CREATE   PROC [dbo].[r_PropertyInvoice] @FromDate SMALLDATETIME = NULL,
                                               @ToDate SMALLDATETIME = NULL
AS
BEGIN

    SET NOCOUNT ON

    CREATE TABLE #PropertyInvoice
    (
        InvoiceNumber NVARCHAR(50),
        InvoiceDate   SMALLDATETIME,
        RentAmount    MONEY,
        BillingMonth  NVARCHAR(50),
        ServiceCharge MONEY,
        OtherCharges  MONEY,
        ParkingFee    MONEY,
        Status        CHAR(50),
        CreatedBy     NVARCHAR(50)
    )

    INSERT INTO #PropertyInvoice
    (InvoiceNumber,
     InvoiceDate,
     RentAmount,
     BillingMonth,
     ServiceCharge,
     OtherCharges,
     ParkingFee,
     Status,
     CreatedBy)


    SELECT InvoiceNumber  as [Invoice Number],
           InvoiceDate    as [Invoice Date],
           RentAmount     as [Rent Amount],
           BillingMonth   as [Billing Month],
           ServicesCharge as [Service Charge],
           OtherCharges   as [Other Charges],
           ParkingFee     as [Parking Fee],
           C.Description  as [Status],
           U.UserID       as [Created By]

    FROM t_rentinvoice R
             JOIN
         t_Users U ON R.CreatedBy = U.Id
             JOIN t_CodeDetails C ON C.Value = R.Status
        AND C.CodeID = 'PropertyInvoiceStatus'

    WHERE (@FromDate IS NULL OR InvoiceDate >= @FromDate)
      AND (@ToDate IS NULL OR InvoiceDate < DATEADD(DAY, 1, @ToDate))

    SELECT * FROM #PropertyInvoice

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
        DB::unprepared("DROP PROCEDURE IF EXISTS r_propertyinvoice");
    }
};
