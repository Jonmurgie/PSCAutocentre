<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
$this->startSetup();

$this->run("

DROP TABLE IF EXISTS `{$this->getTable('amfinder/map')}`;
DROP TABLE IF EXISTS `{$this->getTable('amfinder/value')}`;

CREATE TABLE `{$this->getTable('amfinder/value')}` (
  `value_id`    int(10) unsigned NOT NULL auto_increment,
  `parent_id`   int(10) unsigned NOT NULL,
  `dropdown_id` mediumint(8) unsigned NOT NULL,
  `name`        varchar(255) NOT NULL,
  PRIMARY KEY  (`value_id`),  
  UNIQUE KEY `value_uniq` (`parent_id`, `dropdown_id`,`name`),
  CONSTRAINT `FK_VALUE_DROPDOWN` FOREIGN KEY (`dropdown_id`) REFERENCES {$this->getTable('amfinder/dropdown')} (`dropdown_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('amfinder/map')}` (
  `value_id`    int(10) unsigned NOT NULL,
  `pid`         int(10) unsigned NOT NULL,
  `sku`         varchar(255) NOT NULL,
  UNIQUE KEY  `map_uniq` (`value_id`, `sku`),
  CONSTRAINT `FK_MAP_VALUE` FOREIGN KEY (`value_id`) REFERENCES {$this->getTable('amfinder/value')} (`value_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup(); 