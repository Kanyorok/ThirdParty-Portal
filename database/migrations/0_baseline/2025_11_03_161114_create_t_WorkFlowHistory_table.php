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
        Schema::create('t_WorkFlowHistory', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Source');
            $table->string('SourceID', 100);
            $table->string('Stage', 200);
            $table->decimal('Amount', 20, 4)->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('StatusId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_workfl__3214ec0722bca627');
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_WorkFlowHistory');
    }
};
