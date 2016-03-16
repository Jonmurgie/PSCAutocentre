<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Model_ImportLogHistory extends Amasty_Finder_Model_Import_ImportLogAbstract
{

	public function _construct()
	{
		$this->_init('amfinder/importLogHistory');
	}

	public function getFileState()
	{
		return Amasty_Finder_Helper_Data::FILE_STATE_ARCHIVE;
	}

	public function getFieldInErrorLog()
	{
		return 'import_file_log_history_id';
	}


	public function clearArchive()
	{
		$lifetime = Mage::helper('amfinder')->getArchiveLifetime();
		$date = strftime('%Y-%m-%d %H:%M:%S', strtotime("-{$lifetime} days"));
		$list = $this->getCollection()->addFieldToFilter('ended_at', array("lteq" => $date));
		foreach($list as $item) {
			$item->delete();
		}
	}

	protected function _afterDelete()
	{
		$file = Mage::helper('amfinder')->getFtpImportDir()."archive/".$this->getId().'.csv';

		if(is_file($file)) {
			unlink($file);
		}
		return parent::_afterDelete();
	}
}