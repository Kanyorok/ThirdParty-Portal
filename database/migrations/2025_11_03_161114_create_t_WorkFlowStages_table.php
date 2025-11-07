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
        Schema::create('t_WorkFlowStages', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->smallInteger('Order');
            $table->string('StageName');
            $table->integer('EscalationLimit')->default(0);
            $table->bigInteger('WorkFlowId');
            $table->bigInteger('WorkFlowTypeId');
            $table->bigInteger('WorkFlowLimitId')->nullable();
            $table->bigInteger('PermissionId')->nullable();
            $table->integer('Count')->default(0);
            $table->bigInteger('StatusId')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_workfl__3214ec07eb8f051a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_WorkFlowStages');
    }
};
