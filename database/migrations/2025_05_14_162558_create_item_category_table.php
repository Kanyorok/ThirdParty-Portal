<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('t_ItemCategory', function (Blueprint $table) {
            $table->id('Id');  
            $table->string('CategoryCode', 255)->unique(); 
            $table->string('CategoryName');
            $table->text('Description')->nullable();
            $table->foreignId('ParentId')->nullable()->constrained('t_ItemCategory', 'Id');  
            $table->boolean('Status')->default(true);  
            $table->foreignId('CreatedBy')->nullable()->constrained('t_users', 'Id');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_users', 'Id');
            $table->timestamp('CreatedOn')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->softDeletes('DeletedOn');
  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_ItemCategory');
    }
};