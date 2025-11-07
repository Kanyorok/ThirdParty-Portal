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
        Schema::create('t_ModelRoles', function (Blueprint $table) {
            $table->increments('ModelRoleId');
            $table->string('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('BranchId')->nullable();
            $table->dateTime('CreatedOn', 7)->nullable();
            $table->dateTime('ModifiedOn', 7)->nullable();
            $table->dateTime('DeletedOn', 7)->nullable();

            $table->primary(['ModelRoleId'], 'pk_t_modelroles_modelroleid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ModelRoles');
    }
};
