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
        Schema::table('t_TenderCommittee', function (Blueprint $table) {
            $table->unsignedBigInteger('TenderID')->nullable()->change();
            $table->string('CommitteeType')->after('TenderID')->default('tender');
            $table->unsignedBigInteger('ReferenceId')->after('CommitteeType')->nullable();
        });
    }

    public function down()
    {
        Schema::table('t_TenderCommittee', function (Blueprint $table) {
            $table->unsignedBigInteger('TenderID')->nullable(false)->change();
            $table->dropColumn(['CommitteeType', 'ReferenceId']);
        });
    }
};