<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('t_TenderCategoryItemTypes')) {
            Schema::create('t_TenderCategoryItemTypes', function (Blueprint $table) {
                $table->id('Id');
                $table->unsignedBigInteger('TenderCategoryId');
                $table->unsignedBigInteger('ItemTypeId');
                $table->boolean('IsActive')->default(1);

                $table->index(['TenderCategoryId']);
                $table->index(['ItemTypeId']);
                $table->unique(['TenderCategoryId','ItemTypeId'], 'uq_tendercategory_itemtype');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_TenderCategoryItemTypes');
    }
};

