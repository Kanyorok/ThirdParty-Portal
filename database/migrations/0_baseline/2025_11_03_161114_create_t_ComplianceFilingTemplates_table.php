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
        Schema::create('t_ComplianceFilingTemplates', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 200);
            $table->bigInteger('FilingTypeID');
            $table->bigInteger('RegulatorID');
            $table->bigInteger('FormatID')->nullable();
            $table->string('Frequency', 50)->nullable();
            $table->string('DueDay', 50)->nullable();
            $table->string('PortalURL', 500)->nullable();
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compli__3214ec077afb3e74');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceFilingTemplates');
    }
};
