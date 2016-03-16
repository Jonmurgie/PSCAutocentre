<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


abstract class Amasty_Finder_Model_Import_ImportLogAbstract extends Mage_Core_Model_Abstract
{
	abstract public function getFileState();

	abstract public function getFieldInErrorLog();

	public function getErrorsCollection()
	{
		return Mage::getModel('amfinder/importLogErrors')->getCollection()->addFieldToFilter($this->getFieldInErrorLog(), $this->getId());
	}
}