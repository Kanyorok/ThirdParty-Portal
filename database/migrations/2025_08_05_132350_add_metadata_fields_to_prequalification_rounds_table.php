<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->integer('MaxVendors')->nullable()->after('EndDate');
            $table->string('Status', 2)->default('D')->after('MaxVendors'); 
        });
    }

    public function down(): void
    {
        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->dropColumn(['MaxVendors', 'Status']);
        });
    }
};
