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
        Schema::create('t_LegalDocuments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DocumentTitle')->nullable();
            $table->string('DocumentType', 100)->nullable();
            $table->string('SourceModule', 100)->nullable();
            $table->bigInteger('SourceID');
            $table->bigInteger('LinkedDMSDocID')->nullable();
            $table->string('ReviewStatus', 50)->nullable();
            $table->enum('ExecutionStatus', ['Pending', 'Signed', 'Archived'])->nullable();
            $table->dateTime('DispatchDate')->nullable();
            $table->dateTime('SignOffDate')->nullable();
            $table->bigInteger('ReviewedBy');
            $table->dateTime('ReviewedOn');
            $table->text('Remarks')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legald__3214ec0725a39c9f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalDocuments');
    }
};
