<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ProcurementModes', function (Blueprint $table) {
            $table->id();
            $table->string('Name')->unique();
            $table->text('Description')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->string('UniqueCode', 50)->comment('Unique code for the procurement mode');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementModes');
    }
};
