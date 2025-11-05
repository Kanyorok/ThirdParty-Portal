<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            // Credit application tracking
            $table->boolean('UseCredit')->default(false)->after('ApprovalReason');
            $table->dateTime('CreditAppliedOn')->nullable()->after('UseCredit');
            $table->unsignedBigInteger('CreditAppliedBy')->nullable()->after('CreditAppliedOn');
            $table->text('CreditApplicationReason')->nullable()->after('CreditAppliedBy');

            // Add foreign key constraint
            $table->foreign('CreditAppliedBy')->references('Id')->on('t_Users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            $table->dropForeign(['CreditAppliedBy']);
            $table->dropColumn(['UseCredit', 'CreditAppliedOn', 'CreditAppliedBy', 'CreditApplicationReason']);
        });
    }
};
