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
        Schema::create('t_UserBranch', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('UserId')->constrained('t_Users', 'Id');
            $table->foreignId('BranchId')->constrained('t_Branches', 'Id');
            $table->string('Role'); // e.g., Admin, User, Viewer
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
        Schema::dropIfExists('t__user_branch');
    }
};
