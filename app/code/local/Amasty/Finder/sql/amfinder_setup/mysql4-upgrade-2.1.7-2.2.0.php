<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amfinder/import_file_log')}` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `finder_id` int(10) NOT NULL,
  `started_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  `ended_at` DATETIME NULL DEFAULT NULL,
  `count_lines` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_processing_lines` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_start_processing_line` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_processing_rows` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_errors` int(10) UNSIGNED NOT NULL DEFAULT '0',

  `status` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`file_id`),
  UNIQUE KEY `finder_file` (`file_name`, `finder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
CREATE TABLE `{$this->getTable('amfinder/import_file_log_history')}` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `finder_id` int(10) NOT NULL,
  `started_at` DATETIME NULL DEFAULT NULL,
  `ended_at` DATETIME NULL DEFAULT NULL,
  `count_lines` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_processing_lines` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_processing_rows` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_errors` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
CREATE TABLE `{$this->getTable('amfinder/import_file_log_errors')}` (
  `error_id` int(10) NOT NULL AUTO_INCREMENT,
  `import_file_log_id` int(10) NULL,
  `import_file_log_history_id` int(10) NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `line` int(10) NOT NULL DEFAULT '0',
  `message` VARCHAR(255) NOT NULL DEFAULT '',

  PRIMARY KEY (`error_id`),
  KEY (`import_file_log_id`),
  KEY (`import_file_log_history_id`),
  CONSTRAINT `FK_AMFINDER_IMPORT_FILE_LOG` FOREIGN KEY (`import_file_log_id`) REFERENCES {$this->getTable('amfinder/import_file_log')} (`file_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_AMFINDER_IMPORT_FILE_LOG_HISTORY` FOREIGN KEY (`import_file_log_history_id`) REFERENCES {$this->getTable('amfinder/import_file_log_history')} (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$this->endSetup(); 

