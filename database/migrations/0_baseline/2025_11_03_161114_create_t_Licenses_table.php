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
        Schema::create('t_Licenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('LicenseId', 64)->unique();
            $table->text('PayloadJson');
            $table->string('SignatureBase64', 256);
            $table->string('PublicKeyId', 64);
            $table->tinyInteger('Status')->default(1);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('LastValidatedOn')->nullable();

            $table->primary(['id'], 'pk__t_licens__3213e83f4dff54d5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Licenses');
    }
};
