<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_ThirdPartyType_ThirdParties', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TypeId');
            $table->unsignedBigInteger('ThirdPartyId');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->foreign('TypeId')->references('TypeId')->on('t_ThirdPartyTypes')->onDelete('cascade');
            $table->foreign('ThirdPartyId')->references('Id')->on('t_ThirdParties')->onDelete('cascade');

            $table->unique(['TypeId', 'ThirdPartyId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyType_ThirdParties');
    }
};
