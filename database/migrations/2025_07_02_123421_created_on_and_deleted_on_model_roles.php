<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_ModelRoles', function (Blueprint $table) {
            // CreatedBy FK
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn');

            // ModifiedBy FK
            $table->unsignedBigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            // DeletedBy FK
            $table->unsignedBigInteger('DeletedBy')->nullable();

            // Soft delete column manually
            $table->dateTime('DeletedOn')->nullable();

            // Add foreign key constraints
            $table->foreign('CreatedBy')->references('Id')->on('t_Users');
            $table->foreign('ModifiedBy')->references('Id')->on('t_Users');
            $table->foreign('DeletedBy')->references('Id')->on('t_Users');
        });
    }

    public function down(): void
    {
        Schema::table('t_ModelRoles', function (Blueprint $table) {
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);

            $table->dropColumn([
                'CreatedBy',
                'CreatedOn',
                'ModifiedBy',
                'ModifiedOn',
                'DeletedBy',
                'DeletedOn'
            ]);
        });
    }
};
