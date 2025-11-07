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
        Schema::create('t_ComplianceControls', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->bigInteger('ComplianceAreaID')->nullable();
            $table->bigInteger('ControlTypeID')->nullable();
            $table->bigInteger('OwnerID')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compli__3214ec076f3c8cb2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceControls');
    }
};
