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
        Schema::create('t_ComplianceFilings', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TemplateID')->constrained('t_ComplianceFilingTemplates','Id');
            $table->date('SubmissionDate');
            $table->string('FileName', 255)->nullable();
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->string('Status', 50)->default('Submitted'); // Submitted, Accepted, Rejected, Pending Ack
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('SubmittedBy');
            $table->timestamp('CreatedOn')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceFilings');
    }
};
