DELETE FROM `#__jak2filter_taxonomy_map` WHERE node_id IN (SELECT `id` FROM `#__jak2filter_taxonomy` WHERE `labels` <> '');
DELETE FROM `#__jak2filter_taxonomy` WHERE `labels` <> '';
ALTER TABLE `#__jak2filter_taxonomy`
DROP COLUMN `labels`,
DROP INDEX `asset_idx`,
ADD UNIQUE INDEX `asset_idx` (`asset_id`, `option_id`, `type`);