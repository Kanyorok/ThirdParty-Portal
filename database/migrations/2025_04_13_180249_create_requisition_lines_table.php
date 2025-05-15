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
        Schema::create('t_RequisitionLines', static function (Blueprint $table) {
            $table->id('Id');
            $table->integer('RequisitionID')->nullable();
            $table->string('Type');
            $table->foreignId('Item')->constrained('t_Items', 'Id');
            $table->longText('Description');
            $table->string('UOM');
            $table->float('Quantity')->default(0);
            $table->decimal('ExpectedPrice', 20, 3)->default(0);

            $table->foreignId('UrgencyID')->comment('RequisitionUrgency')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('StatusID')->comment('RequisitionStatus')->constrained('t_CodeDetails', 'ID');
            $table->date('NeededBy');
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
