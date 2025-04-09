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
        Schema::create('t_Leads', static function (Blueprint $table) {
            $table->id('LeadID');
            $table->string('Name');
            $table->string('OtherNames', 200)->nullable();
            $table->string('Email')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Website')->nullable();
            $table->char('Gender', 1)->default('o');//enum
            $table->char('Type', 1)->default('i');//enum
            $table->char('Status', 2)->default('wa');//enum
            $table->string('RelationshipManagerID', 30);
            $table->foreignId('LocationID')->nullable()->constrained('t_Localities', 'ID');
            $table->foreignId('ImageId')->nullable()->constrained('t_CRMImages', 'ImageID');
            $table->foreignId('LeadLossReason')->nullable()->constrained('t_CRMCodeDetails', 'ID');
            $table->foreignId('Industry')->nullable()->constrained('t_CRMCodeDetails', 'ID');
            $table->foreignId('Source')->nullable()->comment('MarketingModes')->constrained('t_CRMCodeDetails', 'ID');
            $table->foreignId('CustomerType')->nullable()->constrained('t_CRMCodeDetails', 'ID');
            $table->string('JobTitle')->nullable();
            $table->dateTime('LastContacted')->nullable();
            $table->longText('Notes')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
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
