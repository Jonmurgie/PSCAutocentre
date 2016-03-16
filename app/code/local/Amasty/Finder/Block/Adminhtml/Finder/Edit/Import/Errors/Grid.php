<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Errors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	/**
	 * @var Amasty_Finder_Model_Import_ImportLogAbstract
	 */
	protected $_file;

	public function __construct()
	{
		parent::__construct();
		$this->setId('import_errors_grid');
		/*$this->setDefaultSort('pos');*/
		$this->setSaveParametersInSession(false);
		//$this->setVarNameFilter('filter_orders');
		$this->setUseAjax(true);

		$this->_file = Mage::registry('amfinder_importFile');
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/finderImport/gridErrors', array('_current'=>true, 'file_id'=>$this->_file->getId(), 'file_state'=>$this->_file->getFileState()));
	}

	protected function _prepareCollection()
	{
		$collection = $this->_file->getErrorsCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}


	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_Finder_Helper_Data */
		$_helper = Mage::helper('amfinder');

		$this->addColumn('created_at', array(
			'header'    => $_helper->__('Created at'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'created_at',
			//'filter' => false,
			'type'		=> 'datetime',
			'time'		=> true,
		));

		$this->addColumn('line', array(
			'header'    => $_helper->__('Line'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'line',
			'type'		=> 'number',
			//'filter' => false,
		));

		$this->addColumn('message', array(
			'header'    => $_helper->__('Message'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'message',
			//'filter' => false,
		));


		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return "";
	}
}