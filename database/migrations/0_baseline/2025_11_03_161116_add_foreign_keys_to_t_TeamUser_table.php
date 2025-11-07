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
        Schema::table('t_TeamUser', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TeamId'])->references(['TeamID'])->on('t_Teams')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserId'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TeamUser', function (Blueprint $table) {
            $table->dropForeign('t_teamuser_createdby_foreign');
            $table->dropForeign('t_teamuser_modifiedby_foreign');
            $table->dropForeign('t_teamuser_teamid_foreign');
            $table->dropForeign('t_teamuser_userid_foreign');
        });
    }
};
