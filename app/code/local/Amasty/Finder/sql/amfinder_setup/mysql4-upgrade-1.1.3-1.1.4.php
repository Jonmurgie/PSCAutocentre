<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 

$this->startSetup();

$this->run("
  ALTER TABLE `{$this->getTable('amfinder/universal')}`
ADD CONSTRAINT `FK_DROPODOWN_UNIVERSAL`
FOREIGN KEY (`finder_id`)
REFERENCES {$this->getTable('amfinder/finder')} (`finder_id`) ON DELETE CASCADE
");

$this->run("

ALTER TABLE  `{$this->getTable('amfinder/finder')}` 
ADD    `custom_url` varchar(255) NOT NULL

");

$this->endSetup(); 

