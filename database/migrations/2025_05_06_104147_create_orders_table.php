<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_Orders', function (Blueprint $table) {
            $table->id('Id');
            $table->integer('DocType')->default(0);
            $table->integer('DocVersion')->default(0);
            $table->integer('DocState')->default(0);
            $table->integer('DocFlag')->default(0);
            $table->string('OrderNo')->default(0);
            $table->string('InvNo')->nullable();
            $table->string('GrvNo')->nullable();
            $table->string('ReqNo')->nullable();
            $table->integer('GrvID')->nullable();
            $table->bigInteger('AccountID')->nullable();
            $table->string('Description')->nullable();
            $table->dateTime('OrderDate')->nullable();
            $table->dateTime('InvDate')->nullable();
            $table->boolean('TaxInclusive')->nullable();
            $table->dateTime('DeliveryDate')->nullable();
            $table->dateTime('ReturnDate')->nullable();
            $table->longText('Message1')->nullable();
            $table->longText('Message2')->nullable();
            $table->longText('Message3')->nullable();
            $table->string('ExtOrdNum')->nullable();
            $table->decimal('InvDisc',18,2)->nullable();
            $table->string('InvDiscReasonID')->nullable();
            $table->decimal('InvDiscAmnt',18,2)->nullable();
            $table->decimal('InvDiscAmntEx',18,2)->nullable();
            $table->decimal('InvTotExclDEx',18,2)->nullable();
            $table->decimal('InvTotTaxDEx',18,2)->nullable();
            $table->decimal('InvTotInclDEx',18,2)->nullable();
            $table->decimal('InvTotExcl',18,2)->nullable();
            $table->decimal('InvTotTax',18,2)->nullable();
            $table->decimal('InvTotIncl',18,2)->nullable();
            $table->decimal('OrdDiscAmnt',18,2)->nullable();
            $table->decimal('OrdDiscAmntEx',18,2)->nullable();
            $table->decimal('OrdTotExclDEx',18,2)->nullable();
            $table->decimal('OrdTotTaxDEx',18,2)->nullable();
            $table->decimal('OrdTotInclDEx',18,2)->nullable();
            $table->decimal('OrdTotExcl',18,2)->nullable();
            $table->decimal('OrdTotTax',18,2)->nullable();
            $table->decimal('OrdTotIncl',18,2)->nullable();
            $table->decimal('fInvDiscAmntForeign',18,2)->nullable();
            $table->decimal('fInvDiscAmntExForeign',18,2)->nullable();
            $table->decimal('fInvTotExclDExForeign',18,2)->nullable();
            $table->decimal('fInvTotTaxDExForeign',18,2)->nullable();
            $table->decimal('fInvTotInclDExForeign',18,2)->nullable();
            $table->decimal('fInvTotExclForeign',18,2)->nullable();
            $table->decimal('fInvTotTaxForeign',18,2)->nullable();
            $table->decimal('fInvTotInclForeign',18,2)->nullable();
            $table->decimal('fOrdDiscAmntForeign',18,2)->nullable();
            $table->decimal('fOrdDiscAmntExForeign',18,2)->nullable();
            $table->decimal('fOrdTotExclDExForeign',18,2)->nullable();
            $table->decimal('fOrdTotTaxDExForeign',18,2)->nullable();
            $table->decimal('fOrdTotInclDExForeign',18,2)->nullable();
            $table->decimal('fOrdTotExclForeign',18,2)->nullable();
            $table->decimal('fOrdTotTaxForeign',18,2)->nullable();
            $table->decimal('fOrdTotInclForeign',18,2)->nullable();
            $table->string('DeliveryNote')->nullable();
            $table->string('Terms')->nullable();
            $table->string('Priority')->nullable();
            $table->string('BranchID')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Orders');
    }
};
