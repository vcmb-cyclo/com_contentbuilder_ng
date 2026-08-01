ALTER TABLE `#__contentbuilderng_storage_fields`
    ADD COLUMN `required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `field_size`;

UPDATE `#__contentbuilderng_storage_fields` AS `fields`
INNER JOIN `#__contentbuilderng_storages` AS `storages`
    ON `storages`.`id` = `fields`.`storage_id`
SET `fields`.`required` = 1
WHERE `storages`.`bytable` = 0
  AND `fields`.`name` IN ('id', 'user_id', 'created', 'created_by', 'modified_user_id', 'modified_by');
