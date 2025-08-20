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
        Schema::create('t_InterBranchRequisition', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ReqNo')->nullable()->unique();
            $table->foreignId('FromBranch')->constrained('t_Branches', 'Id');
            $table->foreignId('ToBranch')->constrained('t_Branches', 'Id');
            //$table->foreignId('ItemCode')->constrained('t_Items', 'Id');
            //$table->foreignId('ItemName')->constrained('t_Items', 'Id');
           // $table->foreignId('UOM')->constrained('t_Items', 'Id');
            //$table->integer('Requested Qty');
            $table->string('Remarks');
            $table->boolean('Status');
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
        Schema::dropIfExists('t_InterBranchRequisition');
    }
};
