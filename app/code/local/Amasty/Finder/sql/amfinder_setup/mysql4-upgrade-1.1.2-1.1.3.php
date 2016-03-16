<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('amfinder/map')}` ADD `id` INT( 10 ) NOT NULL AUTO_INCREMENT FIRST ,ADD PRIMARY KEY ( `id` )
");

$this->endSetup(); 