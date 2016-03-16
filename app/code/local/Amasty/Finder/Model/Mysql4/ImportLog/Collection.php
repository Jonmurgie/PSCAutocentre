<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Model_Mysql4_ImportLog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('amfinder/importLog');
	}

	public function orderForImport()
	{
		$this
			->addOrder('status',self::SORT_ORDER_DESC)
			->addOrder('started_at', self::SORT_ORDER_ASC)
			->addOrder('file_id', self::SORT_ORDER_ASC);
		return $this;
	}
}