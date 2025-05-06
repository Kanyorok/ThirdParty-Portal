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
            $table->decimal('InvDisc')->nullable();
            $table->string('InvDiscReasonID')->nullable();
            $table->decimal('InvDiscAmnt')->nullable();
            $table->decimal('InvDiscAmntEx')->nullable();
            $table->decimal('InvTotExclDEx')->nullable();
            $table->decimal('InvTotTaxDEx')->nullable();
            $table->decimal('InvTotInclDEx')->nullable();
            $table->decimal('InvTotExcl')->nullable();
            $table->decimal('InvTotTax')->nullable();
            $table->decimal('InvTotIncl')->nullable();
            $table->decimal('OrdDiscAmnt')->nullable();
            $table->decimal('OrdDiscAmntEx')->nullable();
            $table->decimal('OrdTotExclDEx')->nullable();
            $table->decimal('OrdTotTaxDEx')->nullable();
            $table->decimal('OrdTotInclDEx')->nullable();
            $table->decimal('OrdTotExcl')->nullable();
            $table->decimal('OrdTotTax')->nullable();
            $table->decimal('OrdTotIncl')->nullable();
            $table->decimal('fInvDiscAmntForeign')->nullable();
            $table->decimal('fInvDiscAmntExForeign')->nullable();
            $table->decimal('fInvTotExclDExForeign')->nullable();
            $table->decimal('fInvTotTaxDExForeign')->nullable();
            $table->decimal('fInvTotInclDExForeign')->nullable();
            $table->decimal('fInvTotExclForeign')->nullable();
            $table->decimal('fInvTotTaxForeign')->nullable();
            $table->decimal('fInvTotInclForeign')->nullable();
            $table->decimal('fOrdDiscAmntForeign')->nullable();
            $table->decimal('fOrdDiscAmntExForeign')->nullable();
            $table->decimal('fOrdTotExclDExForeign')->nullable();
            $table->decimal('fOrdTotTaxDExForeign')->nullable();
            $table->decimal('fOrdTotInclDExForeign')->nullable();
            $table->decimal('fOrdTotExclForeign')->nullable();
            $table->decimal('fOrdTotTaxForeign')->nullable();
            $table->decimal('fOrdTotInclForeign')->nullable();
            $table->string('DeliveryNote')->nullable();
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
