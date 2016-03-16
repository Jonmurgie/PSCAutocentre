<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

class Amasty_Finder_Model_Mysql4_ImportLogErrors extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('amfinder/import_file_log_errors', 'error_id');
	}


	public function archiveErrorHistory($fileId, $historyFileId)
	{
		$adapter = $this->_getWriteAdapter();
		$adapter->update($this->getMainTable(),array('import_file_log_id'=>NULL,'import_file_log_history_id'=>$historyFileId), 'import_file_log_id = '.$fileId);

	}
}