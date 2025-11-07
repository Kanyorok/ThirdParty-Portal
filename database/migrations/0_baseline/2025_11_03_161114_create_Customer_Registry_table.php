<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Customer_Registry', function (Blueprint $table) {
            $table->decimal('RowID', 10, 0);
            $table->string('Source_RCD', 10);
            $table->string('ClientNo', 20);
            $table->string('Client_Name', 200);
            $table->string('ActNo', 20);
            $table->string('Act_Name', 200);
            $table->string('Source_prd', 10);
            $table->dateTime('Act_Open_Date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Customer_Registry');
    }
};
