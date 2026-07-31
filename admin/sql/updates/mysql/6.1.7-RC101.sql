ALTER TABLE `#__contentbuilderng_forms`
    ADD COLUMN `details_template_locked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `details_template`,
    ADD COLUMN `editable_template_locked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `editable_template`;
