
CREATE TABLE IF NOT EXISTS `#__jak2filter_taxonomy` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `option_id` int(5) NOT NULL DEFAULT '0',
  `num_items` int(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_idx` (`asset_id`,`option_id`,`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__jak2filter_taxonomy_map` (
  `node_id` int(10) NOT NULL COMMENT 'taxonomy id',
  `item_id` int(11) NOT NULL COMMENT 'K2 item id',
  `language` char(7) NOT NULL DEFAULT '*',
  UNIQUE KEY `taxonomy_map_id` (`node_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
