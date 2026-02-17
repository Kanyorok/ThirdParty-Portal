<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRDisciplinaryNotices', function (Blueprint $table) {
            $table->unsignedBigInteger('NoticeDocumentId')->nullable();
            $table->string('DeliveryMethod', 20)->nullable();
            $table->string('DeliveryStatus', 30)->nullable();
            $table->unsignedBigInteger('SentBy')->nullable();
            $table->dateTime('SentOn')->nullable();

            $table->foreign('NoticeDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::table('t_HRDisciplinaryResponses', function (Blueprint $table) {
            $table->unsignedBigInteger('NoticeID')->nullable();
            $table->foreign('NoticeID')->references('Id')->on('t_HRDisciplinaryNotices');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRDisciplinaryResponses', function (Blueprint $table) {
            $table->dropForeign(['NoticeID']);
            $table->dropColumn('NoticeID');
        });

        Schema::table('t_HRDisciplinaryNotices', function (Blueprint $table) {
            $table->dropForeign(['NoticeDocumentId']);
            $table->dropColumn('NoticeDocumentId');
            $table->dropColumn('DeliveryMethod');
            $table->dropColumn('DeliveryStatus');
            $table->dropColumn('SentBy');
            $table->dropColumn('SentOn');
        });
    }
};
