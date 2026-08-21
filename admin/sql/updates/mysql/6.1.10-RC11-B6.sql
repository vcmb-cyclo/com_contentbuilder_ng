ALTER TABLE `#__contentbuilderng_elements`
    ADD COLUMN `export_include` TINYINT(1) NOT NULL DEFAULT 1 AFTER `list_include`;

ALTER TABLE `#__contentbuilderng_forms`
    ADD COLUMN `export_id_column` TINYINT(1) NOT NULL DEFAULT 1 AFTER `show_id_column`,
    ADD COLUMN `export_state_column` TINYINT(1) NOT NULL DEFAULT 0 AFTER `export_id_column`,
    ADD COLUMN `export_publish_column` TINYINT(1) NOT NULL DEFAULT 0 AFTER `export_state_column`;

UPDATE `#__contentbuilderng_forms`
SET `export_id_column` = `show_id_column`,
    `export_state_column` = `list_state`,
    `export_publish_column` = `list_publish`;
