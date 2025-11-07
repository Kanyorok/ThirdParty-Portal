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
            $table->bigIncrements('Id');
            $table->bigInteger('TemplateID');
            $table->date('SubmissionDate');
            $table->string('FileName')->nullable();
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->string('Status', 50)->default('Submitted');
            $table->text('Notes')->nullable();
            $table->bigInteger('SubmittedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07074d76fa');
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
