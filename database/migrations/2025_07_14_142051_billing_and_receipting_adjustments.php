<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK on t_RentInvoice.TenantId using known name or raw SQL
        try {
            DB::statement("ALTER TABLE t_RentInvoice DROP CONSTRAINT FK_t_RentInvoice_TenantId");
        } catch (\Throwable $e) {
            // Safe to ignore
        }

        // Drop columns from t_RentInvoice if they exist
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            foreach (['TenantId', 'InvoiceDate', 'RentAmount', 'ServicesCharge', 'OtherCharges'] as $col) {
                if (Schema::hasColumn('t_RentInvoice', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Drop FK on t_RentReceipt.TenantId using known name or raw SQL
        try {
            DB::statement("ALTER TABLE t_RentReceipt DROP CONSTRAINT FK_t_RentReceipt_TenantId");
        } catch (\Throwable $e) {
            // Safe to ignore
        }

        // Drop FK on PaymentMethod if it exists
        try {
            DB::statement("ALTER TABLE t_RentReceipt DROP CONSTRAINT t_rentreceipt_paymentmethod_foreign");
        } catch (\Throwable $e) {
            // Safe to ignore
        }

        // Drop columns from t_RentReceipt
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            foreach (['TenantId', 'PaymentMethod'] as $col) {
                if (Schema::hasColumn('t_RentReceipt', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Re-add columns to t_RentInvoice
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

        // Re-add PaymentMethod to t_RentReceipt
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->unsignedBigInteger('PaymentMethod')->nullable();
            }
        });

        // Add FK on PaymentMethod if CodeID is a key
        $uniqueCodeID = DB::selectOne("
            SELECT COUNT(*) AS is_unique
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
            JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu ON tc.CONSTRAINT_NAME = ccu.CONSTRAINT_NAME
            WHERE tc.TABLE_NAME = 't_CodeDetails'
              AND tc.CONSTRAINT_TYPE IN ('UNIQUE', 'PRIMARY KEY')
              AND ccu.COLUMN_NAME = 'CodeID'
        ");

        if ($uniqueCodeID && $uniqueCodeID->is_unique > 0) {
            Schema::table('t_RentReceipt', function (Blueprint $table) {
                $table->foreign('PaymentMethod')->references('CodeID')->on('t_CodeDetails');
            });
        }
    }

    public function down(): void
    {
        // Drop new columns from t_RentInvoice
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            foreach (['ParkingFee', 'Status'] as $col) {
                if (Schema::hasColumn('t_RentInvoice', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Re-add columns to t_RentInvoice
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

        // Re-add FK on TenantId in t_RentInvoice
        if (
            Schema::hasTable('t_Tenants') &&
            Schema::hasColumn('t_RentInvoice', 'TenantId')
        ) {
            Schema::table('t_RentInvoice', function (Blueprint $table) {
                $table->foreign('TenantId')->references('Id')->on('t_Tenants');
            });
        }

        // Re-add columns to t_RentReceipt
        Schema::table('t_RentReceipt', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentReceipt', 'TenantId')) {
                $table->unsignedBigInteger('TenantId')->nullable();
            }
            if (!Schema::hasColumn('t_RentReceipt', 'PaymentMethod')) {
                $table->unsignedBigInteger('PaymentMethod')->nullable();
            }
        });

        // Re-add FKs
        if (
            Schema::hasTable('t_Tenants') &&
            Schema::hasColumn('t_RentReceipt', 'TenantId')
        ) {
            Schema::table('t_RentReceipt', function (Blueprint $table) {
                $table->foreign('TenantId')->references('Id')->on('t_Tenants');
            });
        }

        $uniqueCodeID = DB::selectOne("
            SELECT COUNT(*) AS is_unique
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
            JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu ON tc.CONSTRAINT_NAME = ccu.CONSTRAINT_NAME
            WHERE tc.TABLE_NAME = 't_CodeDetails'
              AND tc.CONSTRAINT_TYPE IN ('UNIQUE', 'PRIMARY KEY')
              AND ccu.COLUMN_NAME = 'CodeID'
        ");

        if ($uniqueCodeID && $uniqueCodeID->is_unique > 0) {
            Schema::table('t_RentReceipt', function (Blueprint $table) {
                $table->foreign('PaymentMethod')->references('CodeID')->on('t_CodeDetails');
            });
        }
    }
};
