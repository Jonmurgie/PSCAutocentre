<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_Universalimport extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        //create form structure
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp = Mage::helper('amfinder');
        
        $fldSet = $form->addFieldset('amfinder_universalimport', array('legend'=> $hlp->__('Import Universal Products')));
        $fldSet->addField('importuniversal_clear', 'select', array(
          'label'     => $hlp->__('Delete Existing Data'),
          'name'      => 'importuniversal_clear',
          'values'    => array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ))
        ));

        $fldSet->addField('importuniversal_file', 'file', array(
          'label'     => $hlp->__('CSV File'),
          'name'      => 'importuniversal_file',
          'note'      => $hlp->__('SKU1,SKU2,SKU3,...')
        ));               

        return parent::_prepareForm();
    }
}