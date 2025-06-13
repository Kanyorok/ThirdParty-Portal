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
        Schema::create('t_BudgetProductTypes', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ProductCode', 10)->unique();     // Unique and not nullable
            $table->string('Name', 100);                     // Not nullable
            $table->string('Description', 255)->nullable();  // Nullable
            $table->string('CBSCode', 10);                   // Not nullable
            $table->dateTime('LastSyncDate')->nullable();    // Nullable

            $table->dateTime('CreatedOn')->useCurrent();     // Default to current datetime
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_BudgetProductTypes');
    }
};
