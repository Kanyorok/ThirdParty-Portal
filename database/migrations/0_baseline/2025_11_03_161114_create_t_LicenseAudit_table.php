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
        Schema::create('t_LicenseAudit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('EventAt')->useCurrent();
            $table->string('Event', 64);
            $table->string('Detail', 512)->nullable();

            $table->primary(['id'], 'pk__t_licens__3213e83fe0329195');
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
