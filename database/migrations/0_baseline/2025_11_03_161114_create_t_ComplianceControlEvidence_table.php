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
        Schema::create('t_ComplianceControlEvidence', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ControlID');
            $table->string('FileName');
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500);
            $table->integer('Version')->default(1);
            $table->bigInteger('UploadedBy');
            $table->dateTime('UploadedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07e63c126e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceControlEvidence');
    }
};
