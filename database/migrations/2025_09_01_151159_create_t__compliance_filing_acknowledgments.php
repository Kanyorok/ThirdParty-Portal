<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ComplianceFilingAcknowledgments', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('FilingID')->constrained('t_ComplianceFilings', 'Id')->onDelete('cascade');
            $table->string('AckFileName', 255);
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500);
            $table->unsignedBigInteger('UploadedBy');
            $table->timestamp('UploadedOn')->useCurrent();
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
