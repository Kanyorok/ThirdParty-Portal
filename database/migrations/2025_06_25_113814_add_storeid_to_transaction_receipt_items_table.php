<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->foreignId('Store')
                ->nullable()
                ->after('Item')
                ->constrained('t_Stores', 'Id')
                ->onDelete('no action');
        });
    }

    public function down()
    {
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->dropForeign(['Store']);
            $table->dropColumn('Store');
        });
    }
};
