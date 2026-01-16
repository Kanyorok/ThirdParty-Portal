-- Script to permanently delete all soft-deleted inventory types
-- This allows users to create fresh inventory types
-- Run this on the BR_ERP database at 172.17.40.52

USE BR_ERP;
GO

-- Show current inventory types (all soft-deleted)
SELECT 
    Id,
    Type,
    Status,
    CreatedOn,
    DeletedOn
FROM t_InventoryTypes
WHERE DeletedOn IS NOT NULL;
GO

-- Permanently delete all soft-deleted inventory types
DELETE FROM t_InventoryTypes
WHERE DeletedOn IS NOT NULL;
GO

-- Verify deletion
SELECT COUNT(*) AS RemainingRecords
FROM t_InventoryTypes;
GO

PRINT 'All soft-deleted inventory types have been permanently removed.';
PRINT 'Users can now create fresh inventory types.';
GO
