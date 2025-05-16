<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTRFQLinesTable extends Migration
{
    public function up()
    {
        Schema::create('t_RFQLines', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RFQLineNo')->unique();

            $table->foreignId('RFQId')->constrained('t_RFQ')->onDelete('cascade');
            $table->foreignId('ItemId')->constrained('t_Items');
            $table->string('ItemName');
            $table->integer('Quantity');
            $table->string('UnitDescription');
            $table->foreignId('ItemCategoryId')->constrained('t_ItemCategories');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_RFQLines');
    }
}
