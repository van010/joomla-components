DROP TABLE IF EXISTS `#__jamegafilter`;

CREATE TABLE `#__jamegafilter` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `published` INT(1) NOT NULL DEFAULT '1',
    `params` MEDIUMTEXT NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
)

ENGINE =MyISAM
AUTO_INCREMENT =0
DEFAULT CHARSET =utf8;
