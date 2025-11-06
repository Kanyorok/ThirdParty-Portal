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
        Schema::create('t_ComplianceControlEvidence', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ControlID')->constrained('t_ComplianceControls', 'Id')->onDelete('cascade');
            $table->string('FileName', 255);
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500);
            $table->integer('Version')->default(1);

            $table->unsignedBigInteger('UploadedBy');
            $table->timestamp('UploadedOn')->useCurrent();
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
