CREATE TABLE IF NOT EXISTS `#__jak2filter` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `updatetime` datetime NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;