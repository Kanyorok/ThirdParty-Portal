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
        Schema::create('t_ComplianceFilingAcknowledgments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('FilingID');
            $table->string('AckFileName');
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500);
            $table->bigInteger('UploadedBy');
            $table->dateTime('UploadedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07bb2c946c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceFilingAcknowledgments');
    }
};
