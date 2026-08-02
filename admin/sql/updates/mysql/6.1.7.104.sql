-- No `AFTER field_size` here on purpose: field_size was never given its own
-- versioned migration (only install.sql, for fresh installs, and a runtime
-- self-heal in script.php's postflight, which runs after this file). An
-- upgrade from a version that predates field_size would hit "Unknown
-- column 'field_size'" and abort before postflight ever gets a chance to
-- add it. Appending `required` at the end of the table has no functional
-- effect — every query in this codebase addresses columns by name — and
-- postflight's ensureStorageFieldSizeColumn() still adds field_size right
-- after this file runs, for installs that don't already have it.
ALTER TABLE `#__contentbuilderng_storage_fields`
    ADD COLUMN `required` TINYINT(1) NOT NULL DEFAULT 0;

UPDATE `#__contentbuilderng_storage_fields` AS `fields`
INNER JOIN `#__contentbuilderng_storages` AS `storages`
    ON `storages`.`id` = `fields`.`storage_id`
SET `fields`.`required` = 1
WHERE `storages`.`bytable` = 0
  AND `fields`.`name` IN ('id', 'user_id', 'created', 'created_by', 'modified_user_id', 'modified_by');
