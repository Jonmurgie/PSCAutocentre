<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Popup extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		$this->setTemplate('amasty/amfinder/import/popup.phtml');
		return parent::_prepareLayout();
    }
}