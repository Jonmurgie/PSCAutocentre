<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

class Amasty_Finder_Model_Mysql4_ImportLog extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('amfinder/import_file_log', 'file_id');
	}


	public function addUniqueFile($fileName, $finderId)
	{
		$this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), array('file_name'=>$fileName, 'finder_id'=>$finderId));
	}

	public function hasIssetReplaceFile($finderId)
	{
		$db = $this->_getReadAdapter();
		$select = $db->select()->from($this->getMainTable(), "COUNT(*)")->where('finder_id = '.$finderId.' AND file_name = "replace.csv"');
		return (bool) $db->fetchOne($select);
	}
}