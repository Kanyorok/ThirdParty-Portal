<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
    {
        DB::statement("
            CREATE VIEW v_GoodsReceivedHeaderView AS
            SELECT DISTINCT 
            GRNID, 
            POID, 
            FORMAT(ReceivedDate, 'dd/MM/yyyy') AS ReceivedDate, 
            InspectionStatus, 
            ReceivedBy 
            FROM t_GoodsReceipts;
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS V_GoodsReceivedHeaderView");
    }
};
