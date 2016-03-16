<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Created by PhpStorm.
 * User: sumrak
 * Date: 08.09.2015
 * Time: 15:49
 */

class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Errors extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_finder_edit_import_errors';
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