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
        Schema::create('t_Defects', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('InventoryHoldID')->constrained('t_InventoryHold', 'Id');
            $table->foreignId('ItemID')->constrained('t_Items', 'Id');
            $table->foreignId('FromBranch')->nullable()->constrained('t_Branches', 'Id');
            $table->foreignId('Store')->nullable()->constrained('t_Stores', 'Id');
            $table->decimal('Quantity', 18, 2)->default(0);
            $table->foreignId('Defect')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('Condition')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->string('Status')->nullable();
            $table->text('Notes')->nullable();
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
        Schema::dropIfExists('t_Defects');
    }
};
