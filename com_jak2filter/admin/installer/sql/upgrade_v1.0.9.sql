ALTER TABLE `#__jak2filter_taxonomy` ADD COLUMN `labels` VARCHAR(100) NOT NULL AFTER `num_items`,
DROP INDEX `asset_idx`,
ADD UNIQUE INDEX `asset_idx` (`asset_id`, `option_id`, `type`, `labels`);