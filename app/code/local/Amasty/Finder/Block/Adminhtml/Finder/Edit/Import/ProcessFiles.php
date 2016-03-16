<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_ProcessFiles extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_finder_edit_import_processFiles';
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