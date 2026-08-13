ALTER TABLE `#__contentbuilderng_forms`
    ADD COLUMN `initial_order_dir2` VARCHAR(4) NOT NULL DEFAULT 'desc' AFTER `initial_order_dir`,
    ADD COLUMN `initial_order_dir3` VARCHAR(4) NOT NULL DEFAULT 'desc' AFTER `initial_order_dir2`;
