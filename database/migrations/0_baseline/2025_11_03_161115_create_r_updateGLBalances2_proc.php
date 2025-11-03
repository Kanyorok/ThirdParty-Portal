<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("CREATE   PROCEDURE r_updateGLBalances2
AS
BEGIN
    SET NOCOUNT ON;

    -- Update existing records
    UPDATE CB
    SET
        CB.GLAccountID = GL.GLAccountID,
        CB.BalanceDate = GL.CreatedOn,   -- <-- Added missing comma here
        CB.OpeningBalance = 0,
        CB.ClosingBalance = GL.Balance,
        CB.LocalBalance = GL.LocalBalance,
        CB.ForeignBalance = GL.ForeignBalance,
        CB.CreatedBy = GL.CreatedBy,
        CB.CreatedOn = GETDATE(),
        CB.ModifiedBy = GL.ModifiedBy,
        CB.ModifiedOn = GETDATE(),
        CB.DeletedBy = GL.DeletedBy,
        CB.DeletedOn = GL.DeletedOn
    FROM t_GLClosingBalances CB
    INNER JOIN t_FinanceGLBranch GL ON GL.GLAccountID = CB.GLAccountID;

    -- Insert new records only if they don't already exist for today's date
    INSERT INTO t_GLClosingBalances (
        GLAccountID,
        BalanceDate,
        OpeningBalance,
        ClosingBalance,
        LocalBalance,
        ForeignBalance,
        CreatedBy,
        CreatedOn,
        ModifiedBy,
        ModifiedOn,
        DeletedBy,
        DeletedOn
    )
    SELECT
        GL.GLAccountID,
        GL.CreatedOn AS BalanceDate,
        0 AS OpeningBalance,
        GL.Balance,
        GL.LocalBalance,
        GL.ForeignBalance,
        GL.CreatedBy,
        GETDATE(),
        GL.ModifiedBy,
        GETDATE(),
        GL.DeletedBy,
        GETDATE()
    FROM t_FinanceGLBranch GL
    LEFT JOIN t_GLClosingBalances CB ON GL.GLAccountID = CB.GLAccountID
    WHERE CB.GLAccountID IS NULL;
END;



");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_updateGLBalances2");
    }
};
