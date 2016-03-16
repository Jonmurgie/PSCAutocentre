<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_Dropdowns extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Finder_Helper_Data */
        $hlp   = Mage::helper('amfinder');
        $model = Mage::registry('amfinder_finder');

        foreach ($model->getDropdowns() as $drop){
            
        $fldNames[$drop->getId()] = $form->addFieldset('dropdowns'.$drop->getId(), array('legend'=> $hlp->__('Dropdown #%s',$drop->getPos()+1)));    
            
            
            $alias = 'drop_' . $drop->getId(); 
            $fldNames[$drop->getId()]->addField($alias, 'text', array(
                'label'     => $hlp->__('Name'),
                'name'      => $alias,
                'required'  => true,
            )); 
            
            $alias_sort =   'sort_' . $drop->getId(); 
            $fldNames[$drop->getId()]->addField($alias_sort, 'select', array(
                'label'     => $hlp->__('Sort'),
                'name'      => $alias_sort,
                'required'  => true,
                'values'    => array(
                    array(
                        'value' => Amasty_Finder_Helper_Data::SORT_STRING_ASC,
                        'label' => $hlp->__('alphabetically, asc')
                    ),
                    array(
                        'value' =>  Amasty_Finder_Helper_Data::SORT_STRING_DESC,
                        'label' => $hlp->__('alphabetically, desc')
                    ),
                    array(
                        'value' =>  Amasty_Finder_Helper_Data::SORT_NUM_ASC,
                        'label' => $hlp->__('numerically, asc')
                    ),
                    array(
                        'value' =>  Amasty_Finder_Helper_Data::SORT_NUM_DESC,
                        'label' => $hlp->__('numerically, desc')
                    ))               
            ));
               
            $alias_range =   'range_' . $drop->getId(); 
            $fldNames[$drop->getId()]->addField($alias_range, 'select', array(
                'label'     => $hlp->__('Range'),
                'name'      => $alias_range,
                'required'  => true,
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
        
        
        }        
        
        //set form values
        $form->setValues($model->getData()); 
        
        
        return parent::_prepareForm();
    }
}