<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Here, id was ambigous and pointing to the wrong column. CriteriaID should be used instead.
     */
    public function up(): void
    {
        $fk = DB::table('sys.foreign_keys as fk')
            ->join('sys.foreign_key_columns as fkc', 'fk.object_id', '=', 'fkc.constraint_object_id')
            ->join('sys.columns as c', function ($join) {
                $join->on('c.object_id', '=', 'fkc.parent_object_id')
                    ->on('c.column_id', '=', 'fkc.parent_column_id');
            })
            ->where('fk.parent_object_id', DB::raw("OBJECT_ID('t_PrequalificationEvaluations')"))
            ->where('c.name', 'id')
            ->select('fk.name')
            ->value('name');

        if ($fk) {
            DB::statement("ALTER TABLE t_PrequalificationEvaluations DROP CONSTRAINT [$fk]");
        }

        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->foreignId('CriteriaID')
                ->constrained('t_Criterias', 'Id')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->dropForeign(['CriteriaID']);
            $table->dropColumn('CriteriaID');
        });

        Schema::table('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->foreignId('id')
                ->constrained('t_Criterias', 'id')
                ->onDelete('no action');
        });
    }
};
