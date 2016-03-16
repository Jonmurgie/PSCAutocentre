<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */    
class Amasty_Finder_Block_Adminhtml_Finder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_finder';
        $this->_blockGroup = 'amfinder';
        $this->_headerText = Mage::helper('amfinder')->__('Parts Finders');
        $this->_addButtonLabel = Mage::helper('amfinder')->__('Add Finder');
        parent::__construct();
    }
}