<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amfinder/finder')}` (
  `finder_id`  mediumint(8) unsigned NOT NULL auto_increment,
  `cnt`        tinyint(1)   NOT NULL,
  `name`       varchar(255) NOT NULL,
  `template`   varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_descr` text,
  PRIMARY KEY  (`finder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('amfinder/dropdown')}` (
  `dropdown_id` mediumint(8) unsigned NOT NULL auto_increment,
  `finder_id`   mediumint(8) unsigned NOT NULL,
  `pos`         tinyint(1)   NOT NULL,
  `name`        varchar(255) NOT NULL,
  PRIMARY KEY  (`dropdown_id`),
  KEY `finder_id` (`finder_id`),
  CONSTRAINT `FK_DROPODOWN_FINDER` FOREIGN KEY (`finder_id`) REFERENCES {$this->getTable('amfinder/finder')} (`finder_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$this->endSetup(); 