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
        Schema::create('t_RFQSettingCriterias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('SectionID');
            $table->string('CriteriaName', 100);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['id'], 'pk__t_rfqset__3213e83fb7219297');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQSettingCriterias');
    }
};
