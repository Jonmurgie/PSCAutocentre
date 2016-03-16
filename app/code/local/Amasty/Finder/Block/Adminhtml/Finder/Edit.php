<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amfinder';
        $this->_controller = 'adminhtml_finder';
        
        if (Mage::registry('amfinder_finder')->getId()){
            $this->_addButton('save_and_continue', array(
                    'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
                    'onclick'   => 'saveAndContinueEdit()',
                    'class' => 'save'
                ), 10);
            $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";        
        }
        
        $mid = Mage::registry('amfinder_finder')->getId();
        if ($mid) {
            $url = $this->getUrl('adminhtml/value/new', array('finder'=>$mid));  
            $this->_formScripts[] = " function newFinder(){ setLocation('$url'); } ";
        }

		$this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amfinder')->__('New Parts Finder');
        if (Mage::registry('amfinder_finder')->getId()){
            $header = Mage::helper('amfinder')->__('Edit Parts Finder `%s`', Mage::registry('amfinder_finder')->getName());
        }
        return $header;
    }
}