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
        Schema::table('t_Branches', function (Blueprint $table) {
            $table->string('Address')->nullable();
            $table->string('Address2')->nullable();
            $table->string('City')->nullable();
            $table->string('State')->nullable();
            $table->string('Zip')->nullable();
            $table->string('Country')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Fax')->nullable();
            $table->string('Email')->nullable();
            $table->unique('BranchID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Branches', function (Blueprint $table) {
            $table->dropColumn(['Address', 'Address2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Fax', 'Email']);
        });
    }
};
