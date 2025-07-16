<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // === DROP FKs BEFORE DROPPING COLUMNS ON t_RentReceipt ===
        // Drop PaymentMethod FK if exists
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentreceipt_paymentmethod_foreign'
            )
            ALTER TABLE t_RentReceipt DROP CONSTRAINT t_rentreceipt_paymentmethod_foreign
        ");
        // Drop TenantId FK if exists (if ever existed)
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentreceipt_tenantid_foreign'
            )
            ALTER TABLE t_RentReceipt DROP CONSTRAINT t_rentreceipt_tenantid_foreign
        ");

        // === DROP FKs BEFORE DROPPING COLUMNS ON t_RentInvoice ===
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentinvoice_tenantid_foreign'
            )
            ALTER TABLE t_RentInvoice DROP CONSTRAINT t_rentinvoice_tenantid_foreign
        ");

        // === DROP columns on t_RentInvoice if they exist ===
        $invoiceColumnsToDrop = ['TenantId', 'InvoiceDate', 'RentAmount', 'ServicesCharge', 'OtherCharges'];
        foreach ($invoiceColumnsToDrop as $col) {
            if (Schema::hasColumn('t_RentInvoice', $col)) {
                Schema::table('t_RentInvoice', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // === DROP columns on t_RentReceipt if they exist ===
        $receiptColumnsToDrop = ['TenantId', 'PaymentMethod', 'AmountPaid', 'Amount'];
        foreach ($receiptColumnsToDrop as $col) {
            if (Schema::hasColumn('t_RentReceipt', $col)) {
                Schema::table('t_RentReceipt', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // === RE-ADD columns to t_RentInvoice ===
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentInvoice', 'InvoiceDate')) {
                $table->date('InvoiceDate')->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'RentAmount')) {
                $table->decimal('RentAmount', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'ServicesCharge')) {
                $table->decimal('ServicesCharge', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'OtherCharges')) {
                $table->decimal('OtherCharges', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'ParkingFee')) {
                $table->decimal('ParkingFee', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'Status')) {
                $table->string('Status', 1)->nullable();
            }
        });

        // === RE-ADD PaymentMethod and amounts to t_RentReceipt ===
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->unsignedBigInteger('PaymentMethod')->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'AmountPaidSoFar')) {
                $table->decimal('AmountPaidSoFar', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'AmountPaidNow')) {
                $table->decimal('AmountPaidNow', 20, 2)->nullable();
            }
        });

        // === RE-ADD FKs to t_RentReceipt ===
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (Schema::hasTable('t_CodeDetails') && Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->foreign('PaymentMethod')->references('ID')->on('t_CodeDetails');
            }
        });
    }

    public function down(): void
    {
        // === Drop FKs first to avoid issues on rollback ===
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentreceipt_paymentmethod_foreign'
            )
            ALTER TABLE t_RentReceipt DROP CONSTRAINT t_rentreceipt_paymentmethod_foreign
        ");
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentreceipt_tenantid_foreign'
            )
            ALTER TABLE t_RentReceipt DROP CONSTRAINT t_rentreceipt_tenantid_foreign
        ");
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 't_rentinvoice_tenantid_foreign'
            )
            ALTER TABLE t_RentInvoice DROP CONSTRAINT t_rentinvoice_tenantid_foreign
        ");

        // === Drop newly added columns from t_RentInvoice ===
        foreach (['ParkingFee', 'Status'] as $col) {
            if (Schema::hasColumn('t_RentInvoice', $col)) {
                Schema::table('t_RentInvoice', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // === Re-add original columns to t_RentInvoice ===
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentInvoice', 'TenantId')) {
                $table->unsignedBigInteger('TenantId')->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'InvoiceDate')) {
                $table->date('InvoiceDate')->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'RentAmount')) {
                $table->decimal('RentAmount', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'ServicesCharge')) {
                $table->decimal('ServicesCharge', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'OtherCharges')) {
                $table->decimal('OtherCharges', 20, 2)->nullable();
            }
        });

        // === Re-add FK for TenantId on t_RentInvoice ===
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            if (Schema::hasTable('t_Tenants') && Schema::hasColumn('t_RentInvoice', 'TenantId')) {
                $table->foreign('TenantId')->references('Id')->on('t_Tenants');
            }
        });

        // === Re-add original columns to t_RentReceipt ===
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentReceipt', 'TenantId')) {
                $table->unsignedBigInteger('TenantId')->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->unsignedBigInteger('PaymentMethod')->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'AmountPaid')) {
                $table->decimal('AmountPaid', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'Amount')) {
                $table->decimal('Amount', 20, 2)->nullable();
            }
        });

        // === Re-add FKs to t_RentReceipt ===
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (Schema::hasTable('t_CodeDetails') && Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->foreign('PaymentMethod')->references('ID')->on('t_CodeDetails');
            }
        });
    }
};
