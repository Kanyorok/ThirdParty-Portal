<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProcurementPlanStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ProcurementPlans', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ProcurementPeriodId')->constrained('t_ProcurementPeriods')->onDelete('cascade');
            $table->foreignId('ItemId')->constrained('t_Items', 'Id');

            $table->string('Category', 100)->nullable();
            $table->string('UOM', 50)->nullable();

            $table->decimal('Quantity', 12, 2)->nullable();

            $table->string('PlannedQuarter', 10)->nullable();
            $table->date('ExpectedDeliveryDate')->nullable();

            $table->decimal('TotalCost', 12, 2)->nullable();

            //$defaultStatus = ProcurementPlanStatusEnum::Draft->value;
            //$table->string('Status', 50)->default($defaultStatus);

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');

            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_ProcurementPlans');
    }
};
