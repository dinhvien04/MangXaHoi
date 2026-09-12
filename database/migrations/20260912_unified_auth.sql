-- Unify User/Admin authentication and remove the obsolete plaintext-password compatibility column.
-- Safe to run on an existing Handbook database. The dynamic statement makes the migration idempotent.

SET @password_text_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'password_text'
);

SET @drop_password_text_sql = IF(
    @password_text_exists > 0,
    'ALTER TABLE users DROP COLUMN password_text',
    'SELECT 1'
);

PREPARE handbook_stmt FROM @drop_password_text_sql;
EXECUTE handbook_stmt;
DEALLOCATE PREPARE handbook_stmt;
