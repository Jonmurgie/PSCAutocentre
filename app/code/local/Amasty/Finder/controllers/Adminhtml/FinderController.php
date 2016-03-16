<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Adminhtml_FinderController extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Parts finder';
    protected $_modelName = 'finder';
    
    public function indexAction()
    {
        $this->_checkWhitelistBlocks();
		$listInvalidSku = Mage::getModel('amfinder/finder')->getListInvalidSku();
		if ($listInvalidSku){
			$msg = Mage::helper('amfinder')->__('There are no such SKUs in the database: %s ', implode(',', $listInvalidSku));
			Mage::getSingleton('adminhtml/session')->addError($msg);
		}
	    $this->loadLayout(); 
        $this->_setActiveMenu('catalog/amfinder');
        $this->_addContent($this->getLayout()->createBlock('amfinder/adminhtml_' . $this->_modelName)); 	    
 	    $this->renderLayout();
    }

	public function newAction() 
	{
        $this->editAction();
	}
	
    public function editAction() 
    {
		$id     = (int) $this->getRequest()->getParam('id');
		$model  = Mage::getModel('amfinder/' . $this->_modelName)->load($id);

		if ($id && !$model->getId()) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Record does not exist'));
			$this->_redirect('adminhtml/finder/');
			return;
		}

		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
			$model->setId($id);
		}
		$this->_prepareForEdit($model);
		
		Mage::register('amfinder_' . $this->_modelName, $model);

		$this->loadLayout();
		
		$this->_setActiveMenu('catalog/amfinder');
		$this->_title($this->__('Edit'));
		
        $this->_addContent($this->getLayout()->createBlock('amfinder/adminhtml_' . $this->_modelName . '_edit'));
        $this->_addLeft($this->getLayout()->createBlock('amfinder/adminhtml_' . $this->_modelName . '_edit_tabs'));
        
		$this->renderLayout();
	}

	public function saveAction() 
	{
	    $id     = $this->getRequest()->getParam('id');
	    $model  = Mage::getModel('amfinder/' . $this->_modelName);
	    $data = $this->getRequest()->getPost();
		if ($data) {
			$model->setData($data)->setId($id);
			try {
			    
				$model->save();
                
                if (!$id){// first time
                    for ($i=0; $i < $model->getCnt(); ++$i){
                        $drop = Mage::getModel('amfinder/dropdown');
                        $drop->setPos($i);
                        $drop->setFinderId($model->getId());
                        $drop->save();
                    }
                }
                else {
                    foreach ($model->getDropdowns() as $drop){
                        $alias = 'drop_' . $drop->getId();
                        $alias_sort =   'sort_' . $drop->getId();
                        $alias_range =   'range_' . $drop->getId();
                        $drop->setName($model->getData($alias));
                        $drop->setSort($model->getData($alias_sort));
                        $drop->setRange($model->getData($alias_range));
                        $drop->save();                    
                    } 
                    
                    $res = $model->import();   
                    foreach ($res as $errMsg){
                        Mage::getSingleton('adminhtml/session')->addError($errMsg);
                    }
                    
                    $resUniversal = $model->importUniversal();   
                    foreach ($resUniversal as $errMsg){
                        Mage::getSingleton('adminhtml/session')->addError($errMsg);
                    }                    
                    
                }				
				
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				
				$msg = Mage::helper('amfinder')->__($this->_title . ' has been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                
                $skus = $model->updateLinks();
                /*if ($skus){
    				$msg = Mage::helper('amfinder')->__('There are no such SKUs in the database: %s ', join(',', $skus));
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                }*/
                
                if ($this->getRequest()->getParam('continue') || !$id){
                    $this->_redirect('adminhtml/finder/edit', array('id' => $model->getId()));
                }
                else {
                    $this->_redirect('adminhtml/finder');
                }
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('adminhtml/finder/edit', array('id' => $id));
            }	
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Unable to find a record to save'));
        $this->_redirect('adminhtml/finder');
	} 
	
    public function deleteAction()
    {
		$id     = (int) $this->getRequest()->getParam('id');
		$model  = Mage::getModel('amfinder/' . $this->_modelName)->load($id);

		if ($id && !$model->getId()) {
    		Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
			$this->_redirect('adminhtml/finder');
			return;
		}
         
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully deleted'));
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('adminhtml/finder');
    }	
		
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Please select records'));
             $this->_redirect('adminhtml/finder');
             return;
        }
         
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amfinder/' . $this->_modelName)->load($id);
                $model->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('adminhtml/finder');
        
    }
    
    public function productsAction() 
	{
	    $html = $this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_tab_products')->toHtml();
        $this->getResponse()->setBody($html);
	}
    
    public function universalproductsAction() 
    {
        $html = $this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_tab_universalproducts')->toHtml();
        $this->getResponse()->setBody($html);
    }

    

    public function productsCsvAction()
    {
        $content = $this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_tab_products')
            ->getCsvFile();
        $this->_prepareDownloadResponse('products.csv', $content);  
    }
    
    public function universalproductsCsvAction()
    {
        $content = $this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_tab_universalproducts')
            ->getCsvFile();
        $this->_prepareDownloadResponse('universalproducts.csv', $content);  
    } 
    
    public function univdeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Unable to find a product to delete'));
            $this->_redirect('adminhtml/finder/index');
            return;
        }
        
        try {
            $universal = Mage::getModel('amfinder/universal')->load($id);
            $finderId = $universal->getFinderId();
            
            $universal->delete();
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('Product has been deleted'));
            $this->_redirect('adminhtml/finder/edit', array('id'=>$finderId, 'active_tab'=>'universalproducts'));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/finder');
        }        
    }  
     
    public function proddeleteAction()
    {
        $id = $this->getRequest()->getParam('vid');
        //	$sku = $this->getRequest()->getParam('sku');
        if (!$id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Unable to find a product to delete'));
            $this->_redirect('adminhtml/finder');
            return;
        }
        
        try {

            $model  = Mage::getModel('amfinder/' . $this->_modelName);
			$newId = $model -> newSetterId($id);
            $model -> deleteMapRow($id);

            $currentId = $newId;
            $value = Mage::getModel('amfinder/value')->load($currentId);
			$dropdownId =  $value->getDropdownId();	
            while (	($currentId) && ($model->isDeletable($currentId))){
                $value = Mage::getModel('amfinder/value')->load($currentId);
                $currentId = $value->getParentId();
                $dropdownId =  $value->getDropdownId();
                $value->delete();       
            }
            
            $dropdown =  Mage::getModel('amfinder/dropdown')->load($dropdownId);
            $finderId = $dropdown->getFinderId();
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('Product has been deleted'));
            $this->_redirect('adminhtml/finder/edit', array('id'=>$finderId, 'active_tab'=>'products'));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/finder/index');
        }        
    }  
       
    protected function _prepareForEdit($model)
    {

        foreach ($model->getDropdowns() as $drop){
            $alias = 'drop_' . $drop->getId();
            $alias_sort =   'sort_' . $drop->getId();
            $alias_range =   'range_' . $drop->getId();           
            
            $model->setData($alias, $drop->getName());
            $model->setData($alias_sort, $drop->getSort());
            $model->setData($alias_range, $drop->getRange());                        
        }    
        
        return true;
    }
    
    protected function _title($text = null, $resetIfExists = true)
    {
        if (version_compare(Mage::getVersion(), '1.4.2') < 0){ 
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }  

    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))->_title($this->__(ucwords($this->_title) . 's'));	 
        return $this;
    }

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('catalog/amfinder');
    }

    protected function _checkWhitelistBlocks()
    {
        $blockList = array('amfinder/form');
        foreach ($blockList as $blockName) {
            try {
                /** @var Mage_Admin_Model_Block $block */
                $block = Mage::getModel('admin/block');
                if(!$block) {
                    return;
                }
                $block->load($blockName, 'block_name');
                if (!$block->getId()) {
                    $block->setData(array(
                        'block_name' => $blockName,
                        'is_allowed' => 1,
                    ));
                    $block->save();
                }
            } catch (Exception $e) {
                // Magento version before 1.9.2.2: operation not required
            }
        }
    }
}
