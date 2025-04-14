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
        Schema::create('t_RequisitionLines', function (Blueprint $table) {
            $table->id('Id');
            $table->integer('RequisitionID')->nullable();
            $table->string('Module');
            $table->string('Item');
            $table->longText('Description');
            $table->string('UOM');
            $table->float('Quantity')->default(0);
            $table->decimal('ExpectedPrice')->default(0);
            $table->smallInteger('Urgency');
            $table->char('Status', 1)->default('p');
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
        Schema::dropIfExists('t_RequisitionLines');
    }
};
