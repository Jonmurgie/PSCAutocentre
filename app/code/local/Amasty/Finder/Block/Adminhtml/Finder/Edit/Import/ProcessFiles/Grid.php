<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_ProcessFiles_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * @var Amasty_Finder_Model_Finder $_finder
	 */
	protected $_finder = null;

	public function __construct()
	{
		parent::__construct();
		$this->setId('files_list_grid');
		$this->setSaveParametersInSession(false);
		$this->setUseAjax(true);
		$this->setNoFilterMassactionColumn(true);

		$this->_finder = Mage::registry('amfinder_finder');
	}

	public function getMainButtonsHtml()
	{
		$html = $this->getRefreshButtonHtml();
		return $html;
	}

	public function getRefreshButtonHtml()
	{
		return $this->getChildHtml('refresh_button');
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/finderImport/grid', array('_current'=>true, 'finder_id'=>$this->_finder->getId()));
	}

	protected function _prepareCollection()
	{
		Mage::getModel('amfinder/import')->loadNewFilesFromFtp($this->_finder->getId());
		$collection = Mage::getModel('amfinder/importLog')
						->getCollection()
						->addFieldToFilter('finder_id', $this->_finder->getId())
						->orderForImport();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareLayout()
	{
		$this->setChild('refresh_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label'     => Mage::helper('adminhtml')->__('Load Files Uploaded by FTP'),
					'onclick'   => $this->getJsObjectName().'.reload()',
				))
		);
		return parent::_prepareLayout();
	}


	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_Finder_Helper_Data */
		$_helper = Mage::helper('amfinder');


		$this->addColumn('file_name', array(
			'header'    => $_helper->__('Files are sorted by priority'),
			'align'     => 'left',
			'index'     => 'file_name',
			'filter' => false,
			'sortable' => false,
		));

		$this->addColumn('file_path', array(
			'header'    => $_helper->__('Path'),
			'align'     => 'left',
			'getter'     => 'getFilePath',
			'sortable' => false,
			'filter' => false,
		));

		$this->addColumn('state', array(
			'header'    => $_helper->__('State'),
			'align'     => 'left',
			'getter'     => 'getState',
			'filter' => false,
			'sortable' => false,
		));

		$this->addColumn('count_errors', array(
			'header'    => $_helper->__('Errors'),
			'align'     => 'left',
			'index'     => 'count_errors',
			'filter' => false,
			'renderer'	=> 'Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Renderer_ImportErrors',
			'sortable' => false,
		));


		$this->addColumn('mode', array(
			'header'    => $_helper->__('Mode'),
			'align'     => 'left',
			'getter'     => 'getMode',
			'filter' => false,
			'sortable' => false,
		));

		$this->addColumn('run', array(
			'header'    => $_helper->__('Run'),
			'align'     => 'left',
			'width'     => '50px',
			//'index'     => 'run',
			'renderer'	=> 'Amasty_Finder_Block_Adminhtml_Finder_Edit_Import_Renderer_RunButton',
			'filter' => false,
			'sortable' => false,
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
							'base' => '*/finderImport/delete',
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
			)
		);

		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return "";
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('file_id');
		$this->getMassactionBlock()->setFormFieldName('file_ids');

		$actions = array(
			'massDelete'     => 'Delete',
		);
		foreach ($actions as $code => $label){
			$this->getMassactionBlock()->addItem($code, array(
				'label'    => Mage::helper('amfinder')->__($label),
				'url'      => $this->getUrl('*/finderImport/' . $code, array('finder_id'=>$this->_finder->getId())),
				'confirm'  => ($code == 'massDelete' ? Mage::helper('amfinder')->__('Are you sure?') : null),
			));
		}
		return $this;
	}

}