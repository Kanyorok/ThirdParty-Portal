<?php
// filepath: database/migrations/xxxx_xx_xx_xxxxxx_make_itemcode_nullable_in_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->string('ItemCode')->nullable()->change();
        });
    }

    public function down(): void
    {

    }
};
