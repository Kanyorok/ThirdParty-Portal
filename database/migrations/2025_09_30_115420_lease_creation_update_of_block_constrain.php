<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            // Drop the wrong foreign key first
            $table->dropForeign(['BlockID']);

            // Add the correct foreign key
            $table->foreign('BlockID')
                ->references('Id')
                ->on('t_PropertyBlock');
        });
    }

    public function down()
    {
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->dropForeign(['BlockID']);
            $table->foreign('BlockID')
                ->references('Id')
                ->on('t_PropertyRegistry'); // rollback to old
        });
    }

};
