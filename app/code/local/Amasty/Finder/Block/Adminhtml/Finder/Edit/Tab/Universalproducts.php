<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_Universalproducts extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_finder = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('amfinderProduct');
        $this->setUseAjax(true);
    }
    
    public function getFinder()
    {
        if (is_null($this->_finder)){
            $id = $this->getRequest()->getParam('id');
            $this->_finder = Mage::getModel('amfinder/finder')->load($id);
        }
        return $this->_finder;        
    }

    protected function _prepareCollection()
    {
        $products = Mage::getModel('amfinder/universal')->getCollection()->addFieldToFilter('finder_id', array('eq' =>$this->getFinder()->getId())) ;

        
        $this->setCollection($products);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {  
        $this->addColumn('sku', array(
            'header'    => Mage::helper('amfinder')->__('SKU'),
            'index'     => 'sku',
        ));
        
        $this->addColumn('action', array(
                    'header'    => Mage::helper('amfinder')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('amfinder')->__('Delete'),
                            'url'     => array('base'=>'*/*/univdelete'),
                            'field'   => 'id',
                            'confirm' => Mage::helper('amfinder')->__('Are you sure?')

                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
        )); 
        
        $this->addExportType('*/*/universalproductsCsv', Mage::helper('amfinder')->__('CSV'));
                
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return null; 
    }
      
}