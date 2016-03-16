<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_ImportHistory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * @var Amasty_Finder_Model_Finder $_finder
	 */
	protected $_finder = null;

	public function __construct()
	{
		parent::__construct();
		$this->setId('import_history_grid');
		$this->setSaveParametersInSession(false);
		$this->setUseAjax(true);
		$this->setDefaultSort('ended_at');

		$this->_finder = Mage::registry('amfinder_finder');
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/finderImport/gridHistory', array('_current'=>true, 'finder_id'=>$this->_finder->getId()));
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amfinder/importLogHistory')->getCollection()->addFieldToFilter('finder_id', $this->_finder->getId());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}


	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_Finder_Helper_Data */
		$_helper = Mage::helper('amfinder');


		$this->addColumn('file_name', array(
			'header'    => $_helper->__('File Name'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'file_name',
			//'filter' => false,
		));

		$this->addColumn('started_at', array(
			'header'    => $_helper->__('Started'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'started_at',
			//'filter' => false,
			'type'		=> 'datetime',
			'time'		=> true,
		));

		$this->addColumn('ended_at', array(
			'header'    => $_helper->__('Finished'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'ended_at',
			//'filter' => false,
			'type'		=> 'datetime',
			'time'		=> true,
		));

		$this->addColumn('errors', array(
			'header'    => $_helper->__('Errors'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'count_errors',
			'filter' => false,
			'renderer'	=> 'Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Renderer_ImportErrors',
		));


		$this->addColumn(
			'action', array(
				'header'   => $_helper->__('Action'),
				'width'    => '50px',
				'type'     => 'action',
				'getter'   => 'getId',
				//'isSystem'	=> true,
				'actions'  => array(

					array(
						'caption' => $_helper->__('Delete'),
						'url'     => array(
							'base' => '*/finderImport/deleteHistory',
							'params' => array('finder_id' => $this->_finder->getId())
						),
						'field'   => 'file_id',
						'confirm' => $_helper->__(
							'Are you sure?'
						),
					)
				),
				'filter'   => false,
				'sortable' => false,
				//'index'     => 'stores',
			)
		);

		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return "";
	}
}