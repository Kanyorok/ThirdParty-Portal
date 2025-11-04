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
        Schema::create('t_Leads', function (Blueprint $table) {
            $table->bigIncrements('LeadID');
            $table->string('Name');
            $table->string('OtherNames', 200)->nullable();
            $table->string('Email')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Website')->nullable();
            $table->char('Gender', 1)->default('o');
            $table->char('Type', 1)->default('i');
            $table->char('Status', 2)->default('wa');
            $table->string('RelationshipManagerID', 30);
            $table->bigInteger('LocationID')->nullable();
            $table->bigInteger('ImageId')->nullable();
            $table->bigInteger('LeadLossReason')->nullable();
            $table->bigInteger('Industry')->nullable();
            $table->bigInteger('Source')->nullable();
            $table->bigInteger('CustomerType')->nullable();
            $table->string('JobTitle')->nullable();
            $table->dateTime('LastContacted')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ApplicationID')->nullable();
            $table->dateTime('ArchivedOn')->nullable();
            $table->bigInteger('ArchivedBy')->nullable();
            $table->bigInteger('CountryId')->nullable();

            $table->primary(['LeadID'], 'pk__t_leads__73ef791a82849cb9');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Leads');
    }
};
