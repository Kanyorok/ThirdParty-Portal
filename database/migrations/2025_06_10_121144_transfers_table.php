<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_Transfers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TransferID')->nullable();
            $table->foreignId('RequisitionId')->constrained('t_InterBranchRequisition', 'Id');
            $table->date('TransferDate')->nullable();
            $table->string('TransferredBy')->nullable();
            $table->string('Status')->nullable();
            $table->foreignId('FromBranch')->constrained('t_Branches', 'Id');
            $table->foreignId('ToBranch')->constrained('t_Branches', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_Transfers');
    }
};
