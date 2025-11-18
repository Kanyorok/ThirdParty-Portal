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
        Schema::create('t_Instance', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->uuid('DbGuid')->unique();
            $table->string('HostFingerprint', 256);
            $table->bigInteger('MaxSeenNonce')->default(0);
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_instan__3214ec071de664c1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Instance');
    }
};