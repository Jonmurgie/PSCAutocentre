<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
$this->startSetup();

$this->run("

DROP TABLE IF EXISTS `{$this->getTable('amfinder/universal')}`;

CREATE TABLE `{$this->getTable('amfinder/universal')}` (
  `universal_id` int(10) NOT NULL AUTO_INCREMENT,
  `finder_id` int(10) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `pid` int(10) NOT NULL,
  PRIMARY KEY (`universal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup(); 