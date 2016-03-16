<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Value_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amfinder';
        $this->_controller = 'adminhtml_value';
        
        $this->_removeButton('back'); 
        $this->_removeButton('reset'); 
        $this->_removeButton('delete'); 
    }

    public function getHeaderText()
    {
        return Mage::helper('amfinder')->__('Option Properties');

    }
}