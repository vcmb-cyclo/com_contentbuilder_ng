ALTER TABLE `#__contentbuilderng_forms`
    ADD COLUMN `list_state_bulk` tinyint(1) NOT NULL DEFAULT '0' AFTER `list_state`;

UPDATE `#__contentbuilderng_forms`
SET `list_state_bulk` = `list_state`;
