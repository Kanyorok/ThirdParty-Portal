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
        Schema::create('t_InventoryHold', function (Blueprint $table) {
            $table->id('Id');
            $table->string('InventoryHoldID')->unique()->nullable();
            $table->foreignId('ItemID')->constrained('t_Items', 'Id');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id');
            $table->foreignId('Store')->nullable()->constrained('t_Stores', 'Id');
            $table->decimal('Quantity', 18, 2)->default(0);
            $table->string('Reason')->nullable();
            $table->foreignId('Source')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->integer('SourceID')->nullable();
            $table->string('Status')->nullable();
            $table->text('Remarks')->nullable();
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
        Schema::dropIfExists('t_InventoryHold');
    }
};
