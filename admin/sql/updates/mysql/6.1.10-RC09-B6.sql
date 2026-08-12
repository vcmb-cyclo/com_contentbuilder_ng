ALTER TABLE `#__contentbuilderng_forms`
    ADD COLUMN `maximum_records` INT NOT NULL DEFAULT 0 AFTER `initial_list_limit`;

ALTER TABLE `#__contentbuilderng_elements`
    ADD COLUMN `detail_include` TINYINT(1) NOT NULL DEFAULT 1 AFTER `linkable`;
