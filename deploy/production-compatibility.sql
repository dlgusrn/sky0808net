-- PREPARED ONLY. Apply after a verified production backup and explicit migration review.
-- Existing rows remain intact; legacy code can continue with this nullable column.
-- The statement checks metadata so rerunning does not add a duplicate column.
SET @sky_compat_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'preacher_list' AND COLUMN_NAME = 'update_date'),
  'SELECT ''preacher_list.update_date already exists'' AS status',
  'ALTER TABLE preacher_list ADD COLUMN update_date DATETIME NULL DEFAULT NULL'
);
PREPARE sky_compat_statement FROM @sky_compat_sql;
EXECUTE sky_compat_statement;
DEALLOCATE PREPARE sky_compat_statement;
