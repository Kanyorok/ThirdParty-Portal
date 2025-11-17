<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_ItemTypes', function (Blueprint $table) {
            $table->dropColumn('TypeName');
        });

        Schema::table('t_ItemTypes', function (Blueprint $table) {
            $table->foreignId('TypeName')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_ItemTypes', function (Blueprint $table) {
            $table->dropForeign(['TypeName']);
            $table->dropColumn('TypeName');
        });

        Schema::table('t_ItemTypes', function (Blueprint $table) {
            $table->string('TypeName')->after('Id');
        });
    }
};
