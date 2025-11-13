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
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->foreignId('BranchId')->nullable()->constrained('t_Branches', 'Id');

            $table->dropIndex(['session_version']);
            $table->dropIndex(['current_session_id']);
            $table->dropColumn(['session_version', 'current_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BranchId');

            $table->string('current_session_id', 255)->nullable()->index();
            $table->unsignedBigInteger('session_version')->default(1)->index();
        });
    }
};
