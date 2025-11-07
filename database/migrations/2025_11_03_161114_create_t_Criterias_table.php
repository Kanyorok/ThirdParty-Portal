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
        Schema::create('t_Criterias', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('SectionID');
            $table->string('CriteriaName', 100);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(true)->index();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable()->index();

            $table->primary(['Id'], 'pk__t_criter__3214ec07eecc3fc7');
            $table->unique(['SectionID', 'CriteriaName']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Criterias');
    }
};
