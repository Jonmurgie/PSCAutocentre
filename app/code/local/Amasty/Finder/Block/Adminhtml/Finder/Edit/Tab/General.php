<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Finder_Helper_Data */
        $hlp   = Mage::helper('amfinder');
        $model = Mage::registry('amfinder_finder');
        
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Title'),
            'name'      => 'name',
            'required'  => true,
        ));         
        
        if (!$model->getId()){
            $fldInfo->addField('cnt', 'text', array(
                'label'     => $hlp->__('Number of Dropdowns'),
                'name'      => 'cnt',
                'required'  => true,
                'class'     => 'validate-greater-than-zero',
            ));
        }

        $fldInfo->addField('template', 'text', array(
            'label'    => $hlp->__('Template'),
            'name'      => 'template',
            'note'      => $hlp->__('E.g. `vertical`, `horizontal`, `responsive`. Leave blank to use a default template'),
        ));                

        $fldInfo->addField('custom_url', 'text', array(
            'label'    => $hlp->__('Custom Destination URL'),
            'name'      => 'custom_url',
            'note'      => $hlp->__('E.g. special-category.html  In most cases you don`t need to set it. Useful when you have 2 or more finders and want to show search results at specific categories. It`s NOT the url key. You can modify /amfinder/ url key in app/code/local/Amasty/Finder/etc/config.xml'),
        ));          
        //set form values
        $form->setValues($model->getData()); 
        
        return parent::_prepareForm();
    }
}