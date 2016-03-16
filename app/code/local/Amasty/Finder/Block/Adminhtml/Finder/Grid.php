<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */
class Amasty_Finder_Block_Adminhtml_Finder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('finderGrid');
        $this->setDefaultSort('finder_id');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amfinder/finder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amfinder'); 
        $this->addColumn('finder_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'finder_id',
        ));
        
        $this->addColumn('name', array(
            'header'    => $hlp->__('Name'),
            'index'     => 'name',
        ));
        
        $this->addColumn('cnt', array(
            'header'    => $hlp->__('Number of Dropdowns'),
            'index'     => 'cnt',
        ));                     
    
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/finder/edit', array('id' => $row->getId()));
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('finder_id');
        $this->getMassactionBlock()->setFormFieldName('finders');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amfinder')->__('Delete'),
             'url'      => $this->getUrl('adminhtml/finder/massDelete'),
             'confirm'  => Mage::helper('amfinder')->__('Are you sure?')
        ));
        
        return $this; 
    }
}