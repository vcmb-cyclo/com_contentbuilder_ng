ALTER TABLE `#__contentbuilderng_storage_fields`
    ADD COLUMN `required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `field_size`;
