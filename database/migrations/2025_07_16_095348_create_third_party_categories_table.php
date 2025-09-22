<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ThirdPartiesCategories', function (Blueprint $table) {
            $table->id('MappingID');
            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties', 'Id')->onDelete('cascade');
            $table->foreignId('CategoryID')->constrained('t_CodeDetails', 'Id')->onDelete('cascade');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartiesCategories');
    }
};
