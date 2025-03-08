DROP TABLE IF EXISTS `#__jalang_translated`;
CREATE TABLE IF NOT EXISTS `#__jalang_translated` (
    id int(11) NOT NULL AUTO_INCREMENT,
    adapter VARCHAR(40) NOT NULL,
    elementid TEXT(125) NOT NULL,
    date_translate DATETIME,
    language VARCHAR(20) NOT NULL,
    
    PRIMARY KEY(`id`)
) ENGINE = MyISAM CHARSET=utf8 AUTO_INCREMENT=0;