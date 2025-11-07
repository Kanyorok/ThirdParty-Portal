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
        Schema::create('t_LicenseAudit', static function (Blueprint $table) {
            $table->id();
            $table->dateTime('EventAt')->useCurrent();
            $table->string('Event', 64);
            $table->string('Detail', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LicenseAudit');
    }
};

