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
            $table->bigIncrements('Id');
            $table->integer('DocType')->default(0);
            $table->integer('DocVersion')->default(0);
            $table->integer('DocState')->default(0);
            $table->integer('DocFlag')->default(0);
            $table->string('OrderNo')->default('0');
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
            $table->text('Message1')->nullable();
            $table->text('Message2')->nullable();
            $table->text('Message3')->nullable();
            $table->string('ExtOrdNum')->nullable();
            $table->decimal('InvDisc', 18)->nullable();
            $table->string('InvDiscReasonID')->nullable();
            $table->decimal('InvDiscAmnt', 18)->nullable();
            $table->decimal('InvDiscAmntEx', 18)->nullable();
            $table->decimal('InvTotExclDEx', 18)->nullable();
            $table->decimal('InvTotTaxDEx', 18)->nullable();
            $table->decimal('InvTotInclDEx', 18)->nullable();
            $table->decimal('InvTotExcl', 18)->nullable();
            $table->decimal('InvTotTax', 18)->nullable();
            $table->decimal('InvTotIncl', 18)->nullable();
            $table->decimal('OrdDiscAmnt', 18)->nullable();
            $table->decimal('OrdDiscAmntEx', 18)->nullable();
            $table->decimal('OrdTotExclDEx', 18)->nullable();
            $table->decimal('OrdTotTaxDEx', 18)->nullable();
            $table->decimal('OrdTotInclDEx', 18)->nullable();
            $table->decimal('OrdTotExcl', 18)->nullable();
            $table->decimal('OrdTotTax', 18)->nullable();
            $table->decimal('OrdTotIncl', 18)->nullable();
            $table->decimal('fInvDiscAmntForeign', 18)->nullable();
            $table->decimal('fInvDiscAmntExForeign', 18)->nullable();
            $table->decimal('fInvTotExclDExForeign', 18)->nullable();
            $table->decimal('fInvTotTaxDExForeign', 18)->nullable();
            $table->decimal('fInvTotInclDExForeign', 18)->nullable();
            $table->decimal('fInvTotExclForeign', 18)->nullable();
            $table->decimal('fInvTotTaxForeign', 18)->nullable();
            $table->decimal('fInvTotInclForeign', 18)->nullable();
            $table->decimal('fOrdDiscAmntForeign', 18)->nullable();
            $table->decimal('fOrdDiscAmntExForeign', 18)->nullable();
            $table->decimal('fOrdTotExclDExForeign', 18)->nullable();
            $table->decimal('fOrdTotTaxDExForeign', 18)->nullable();
            $table->decimal('fOrdTotInclDExForeign', 18)->nullable();
            $table->decimal('fOrdTotExclForeign', 18)->nullable();
            $table->decimal('fOrdTotTaxForeign', 18)->nullable();
            $table->decimal('fOrdTotInclForeign', 18)->nullable();
            $table->string('DeliveryNote')->nullable();
            $table->string('Terms')->nullable();
            $table->string('Priority')->nullable();
            $table->string('BranchID')->nullable();
            $table->char('DocStatus', 2)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('SourceType', 16)->nullable();
            $table->bigInteger('SourceId')->nullable();
            $table->string('OriginationType', 50)->nullable()->index();
            $table->string('OriginationRef', 100)->nullable();
            $table->bigInteger('ContractRef')->nullable();
            $table->bigInteger('AwardRef')->nullable();
            $table->bigInteger('PlanRef')->nullable();
            $table->decimal('TotalAmount', 15)->nullable();
            $table->text('Notes')->nullable();
            $table->text('DeliveryTerms')->nullable();
            $table->string('Status', 50)->nullable();

            $table->primary(['Id'], 'pk__t_orders__3214ec0740c1c22f');
            $table->index(['AwardRef', 'Status']);
            $table->index(['ContractRef', 'Status']);
            $table->index(['OriginationType', 'Status']);
            $table->index(['PlanRef', 'Status']);
            $table->unique(['SourceType', 'SourceId'], 'ux_t_orders_source');
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
