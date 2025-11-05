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
        Schema::create('t_ApprovalGroups', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocType')->unique(); // assuming one group per DocType
            $table->string('ApprovalType'); // e.g., AMT, ANY, MAJ, ALL
            $table->unsignedBigInteger('Permission'); // permission id

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');

            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ApprovalGroups');
    }
};
