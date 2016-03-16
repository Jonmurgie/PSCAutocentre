<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Renderer_RunButton extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		/*if ($this->getColumn()->getEditable()) {
			$value = $this->_getValue($row);
			return $value
			. ($this->getColumn()->getEditOnly() ? '' : ($value != '' ? '' : '&nbsp;'))
			. $this->_getInputValueElement($row);
		}*/
		$helper = Mage::helper('amfinder');
		$url = $this->getUrl('*/finderImport/runFile', array('_current'=>true, 'file_id'=>$row->getId()));
		$html = '
		<button
		title="'.$helper->__('Import').'" type="button" class="scalable save '. ($row->getIsLocked() ? 'disabled' : '').'" onclick="amFinderRunImportFile(\''.$url.'\')" style=""
		'. ($row->getIsLocked() ? 'disabled' : '').'>
		<span><span><span>'.$helper->__('Import').'</span></span></span>
		</button>
		';
		return $html;
	}
}