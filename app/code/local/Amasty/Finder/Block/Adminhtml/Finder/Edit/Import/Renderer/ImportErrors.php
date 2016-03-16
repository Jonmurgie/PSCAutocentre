<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Renderer_ImportErrors extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$helper = Mage::helper('amfinder');

		$value = $this->_getValue($row);
		$url = $this->getUrl('*/finderImport/errors', array('file_id'=>$row->getId(), 'file_state'=>$row->getFileState()));
		$html = $value;
		if($value > 0) {
			$html .= ' <a href="javascript:amShowErrorsPopup(\''.$url.'\')">'.$helper->__('View').'</a>';
		}

		return $html;

	}
}