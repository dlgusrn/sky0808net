-- Read-only production compatibility checks. Never run the old db_update.sql as a migration.
SELECT VERSION() AS database_version, @@event_scheduler AS event_scheduler;
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'preacher_list' AND COLUMN_NAME IN ('idx','name','insert_date','update_date'))
    OR (TABLE_NAME = 'member_admin' AND COLUMN_NAME = 'password')
    OR (TABLE_NAME = 'notice' AND COLUMN_NAME = 'title'))
ORDER BY TABLE_NAME, ORDINAL_POSITION;
SHOW CREATE EVENT EV_del_live_entry;
