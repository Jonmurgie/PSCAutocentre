<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_ImportHistory extends Mage_Adminhtml_Block_Widget_Grid_Container//Mage_Adminhtml_Block_Widget //Mage_Adminhtml_Block_Widget_Form
{
	/*protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('amasty/amfinder/import/import.phtml');
	}

	public function getFinderId()
	{
		return Mage::registry('amfinder_finder')->getId();
	}*/

	public function __construct()
	{
		$this->_controller = 'adminhtml_finder_edit_tab_importHistory';
		$this->_blockGroup = 'amfinder';
		$this->_headerText = '';
		parent::__construct();
	}

	public function getButtonsHtml($area = null)
	{
		$this->removeButton('add');
		parent::getButtonsHtml($area);
	}
}