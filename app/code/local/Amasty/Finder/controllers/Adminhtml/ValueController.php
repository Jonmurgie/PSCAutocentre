<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Adminhtml_ValueController extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Cortages';
    protected $_modelName = 'value';
    
    
    public function newAction() 
    {
        $this->editAction();
    }
    
    public function editAction() 
    {
        $newId     = (int) $this->getRequest()->getParam('id');
		$id = Mage::getModel('amfinder/finder')->newSetterId($newId);
		
        $model  = Mage::getModel('amfinder/' . $this->_modelName)->load($id);
        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Record does not exist'));
            $this->_redirect('adminhtml/finder/*');
            return;
        }
        $settingData = array();
        $labelData   = array();
        $dropdownId = $model->getDropdownId();
        
        $currentId = $id;

        
        Mage::register('amfinder_' . $this->_modelName, $model);

        $this->loadLayout();
        
        $this->_setActiveMenu('catalog/amfinder');
        $this->_title($this->__('Edit'));
        
        $this->_addContent($this->getLayout()->createBlock('amfinder/adminhtml_' . $this->_modelName . '_edit'));
        $this->renderLayout();
    }
    
    public function saveAction() 
    {
        $chainId     = $this->getRequest()->getParam('id');
        $finder = $this->getRequest()->getParam('finder');
                   
        $data = $this->getRequest()->getPost();
		$id = Mage::getModel('amfinder/finder')->newSetterId($chainId);
        $currentId = $id;

        if ($chainId || isset($data['new_finder'])) {
            if ($chainId){

                foreach ($data as $element => $arrayValue)
                {
                    if (substr($element, 0, 6) == 'label_')
                    {
                        $valueId = (int)(substr($element,6));
                        $value = Mage::getModel('amfinder/value')->load($valueId);
                        $dropdownId =  $value->getDropdownId();
                        unset($data[$element]);
                        $data['label_'.$dropdownId] = $arrayValue;               
                    }
                }                  
                
                try {
                    $model  = Mage::getModel('amfinder/finder');
                    $newId = $model -> newSetterId($chainId);
                    $model -> deleteMapRow($chainId);

                    $currentId = $newId;
                    $value = Mage::getModel('amfinder/value')->load($currentId);
                    $dropdownId =  $value->getDropdownId();
                    while (    ($currentId) && ($model->isDeletable($currentId))){
                        $value = Mage::getModel('amfinder/value')->load($currentId);
                        $currentId = $value->getParentId();
                        $dropdownId =  $value->getDropdownId();
                        $value->delete();       
                    }
                    
                    $dropdown =  Mage::getModel('amfinder/dropdown')->load($dropdownId);
                    $finderId = $dropdown->getFinderId();
                    
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('Product has been deleted'));
                }
                catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('adminhtml/finder/index');
                }                
                                 
            }

            try {
                $model  = Mage::getModel('amfinder/value');
                $finderId = $model->saveNewFinder($data);
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                $msg = Mage::helper('amfinder')->__('Record have been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);

                $this->_redirect('adminhtml/finder/edit', array('id'=>$finderId, 'active_tab'=>'products'));    
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('adminhtml/value/new');
            }                       
        }    
  
    }

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('catalog/amfinder');
    }
    
}    