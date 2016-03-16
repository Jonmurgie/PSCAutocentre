<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Value_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{   
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form', 
            'action'  => $this->getUrl('adminhtml/value/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        $hlp = Mage::helper('amfinder');
        $model       = Mage::registry('amfinder_value');
        $currentId =  $model->getValueId();
        $settingData = array();
        
        $fldSet = $form->addFieldset('set', array('legend'=> $hlp->__('General')));     
        $fldSet->addField('sku', 'text', array(
            'label'    => Mage::helper('amfinder')->__('SKU'),
            'name'     => 'sku',
        ));
        
        $value = Mage::getModel('amfinder/value')->load($currentId);
        if ($currentId)
        {
            $settingData['sku'] = $value->getSkuById($this->getRequest()->getParam('id'), $currentId);             
        }

              
        while ($currentId){
            $alias_name  =   'name_' . $currentId; 
            $alias_label =   'label_'.$currentId; 
             
                $value = Mage::getModel('amfinder/value')->load($currentId);
                $currentId = $value->getParentId();
                $dropdownId =  $value->getDropdownId();
                $dropdown =  Mage::getModel('amfinder/dropdown')->load($dropdownId);
                $dropdownName = $dropdown->getName();
                $settingData[$alias_name] = $value->getName();
            $fldSet->addField($alias_name, 'text', array(
                'label'    => Mage::helper('amfinder')->__($dropdownName),
                'name'     => $alias_label
            ));                           
        } 
        
        $finderId = $this->getRequest()->getParam('finder');
        if ($finderId){
            $finder = Mage::getModel('amfinder/finder')->load($finderId);
                 
            foreach ($finder->getDropdowns() as $drop){
                $alias_name  = 'name_'.$drop->getId();
                $alias_label = 'label_'.$drop->getId();
                $fldSet->addField($alias_name, 'text', array(
                    'label'    => Mage::helper('amfinder')->__($drop->getName()),
                    'name'     => $alias_label
                ));              
            }
            
            $fldSet->addField('new_finder', 'hidden', array(
                'name'     => 'new_finder'
            ));
            $settingData['new_finder'] = 1;                           
        }
 
        //set form values 
        $form->setValues($settingData); 
               
        return parent::_prepareForm();
    }
}
