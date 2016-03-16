<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Model_ImportLogErrors extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		$this->_init('amfinder/importLogErrors');
	}


	public function error($fileId, $line, $message)
	{
		$this
			->setImportFileLogId($fileId)
			->setCreatedAt(date('Y-m-d H:i:s'))
			->setLine($line)
			->setMessage($message)
			->save();
	}

	public function archiveErrorHistory($fileId, $historyFileId)
	{
		$this->getResource()->archiveErrorHistory($fileId, $historyFileId);
	}
}