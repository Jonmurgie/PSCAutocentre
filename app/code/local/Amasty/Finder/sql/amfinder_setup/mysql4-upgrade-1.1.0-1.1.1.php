<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
$this->startSetup();

$this->run("

ALTER TABLE  `{$this->getTable('amfinder/dropdown')}` 
ADD  `sort` TINYINT( 2 ) NOT NULL ,
ADD  `range` TINYINT( 2 ) NOT NULL

");

$this->endSetup(); 