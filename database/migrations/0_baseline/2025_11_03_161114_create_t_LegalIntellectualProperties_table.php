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
        Schema::create('t_LegalIntellectualProperties', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Title');
            $table->string('IPType');
            $table->string('RegistrationNumber', 100);
            $table->date('RegistrationDate');
            $table->date('ExpiryDate');
            $table->string('Status')->default('Inactive');
            $table->string('Owner');
            $table->bigInteger('DMSDocID')->nullable();
            $table->text('Remarks')->nullable();
            $table->boolean('IsDisputed')->default(false);
            $table->text('DisputeReason')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legali__3214ec072918acad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalIntellectualProperties');
    }
};
