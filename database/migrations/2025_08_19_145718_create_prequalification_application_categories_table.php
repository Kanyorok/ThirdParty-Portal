<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_PrequalificationApplicationCategories', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ApplicationID');
            $table->unsignedBigInteger('CategoryID');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->timestamp('ModifiedOn')->useCurrent()->nullable();

            $table->foreign('ApplicationID')
                ->references('ApplicationID')
                ->on('t_SupplierPrequalificationApplications')
                ->onDelete('cascade');

            $table->foreign('CategoryID')
                ->references('SupplierCategoryID')
                ->on('t_SupplierCategories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationApplicationCategories');
    }
};
