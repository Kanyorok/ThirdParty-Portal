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
        Schema::create('t_ThirdPartyTypes', function (Blueprint $table) {
            $table->bigIncrements('TypeId');
            $table->unsignedBigInteger('Type')->index();
            $table->unsignedBigInteger('FinanceRole')->nullable()->index();

            $table->foreign('Type')
                ->references('Id')
                    ->on('t_CategoryMaster');

            $table->foreign('FinanceRole')
                ->references('FinanceRoleID')
                    ->on('t_FinanceRoles');
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
        Schema::dropIfExists('t_ThirdPartyTypes');
    }
};
