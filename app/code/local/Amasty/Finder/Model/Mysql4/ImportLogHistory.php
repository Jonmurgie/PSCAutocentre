<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

class Amasty_Finder_Model_Mysql4_ImportLogHistory extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('amfinder/import_file_log_history', 'file_id');
	}




}