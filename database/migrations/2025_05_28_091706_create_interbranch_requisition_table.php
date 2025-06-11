<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('t_InterBranchRequisition', static function (Blueprint $table) {
    $table->id('Id');
    $table->string('ReqNo')->nullable(); 
    $table->foreignId('FromBranch')->constrained('t_Branches', 'Id');
    $table->foreignId('ToBranch')->constrained('t_Branches', 'Id');
    $table->boolean('Status')->nullable();
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
        Schema::dropIfExists('t_InterBranchRequisition');
    }
};
