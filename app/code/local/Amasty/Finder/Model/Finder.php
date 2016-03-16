<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Model_Finder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amfinder/finder');
    }
    
    public function getDropdowns()
    {
        $collection = Mage::getModel('amfinder/dropdown')->getResourceCollection()
            ->addFieldToFilter('finder_id', $this->getId());
        $collection->getSelect()->order('pos');
            
        return $collection;     
    }
    
    public function saveFilter($dropdowns, $categoryId = 0)
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();

        if (!$dropdowns)
            return false;
            
        if (!is_array($dropdowns))
            return false;
         
        $safeValues = array();
        $id      = 0;
        $current = 0;      
        foreach ($this->getDropdowns() as $d){
            $id = $d->getId();
            $safeValues[$id] = isset($dropdowns[$id]) ? $dropdowns[$id] : 0;
            if  (isset($dropdowns[$id]) && ($dropdowns[$id])){
               $current = $dropdowns[$id];
            }      
        }  
        
        if ($id) {
            $safeValues['last']    = $safeValues[$id];
            $safeValues['current'] = $current; 
        }
        
        $safeValues['filter_category_id'] = $categoryId;            
        $session->setData($name, $safeValues); 
      
        return true; 
    } 
    
    public function resetFilter()
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();

        $session->setData($name, null);
        return true;        
    }       
    
    public function applyFilter()
    {
        $id = $this->getSavedValue('current');
        if (!$id){
            return false;
        }
        
        if (!$this->isAllowedInCategory()){
            return false;
        }       
        
        $finderId = $this->getId();

        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch')
            $layer = Mage::getSingleton('catalogsearch/layer');
        else
            $layer = Mage::getSingleton('catalog/layer');
        

        $collection = $layer->getProductCollection();
        $cnt = $this->countEmptyDropdowns();
        $this->getResource()->addConditionToProductCollection($collection, $id, $cnt, $finderId);

        return true;
    }
    
    public function getSavedValue($dropdownId)
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();
        
        $values = $session->getData($name);

        if (!is_array($values))
            return 0;
            
        if (empty($values[$dropdownId]))
            return 0;
            
        return $values[$dropdownId];   
    }
    
    public function import()
    {
        return $this->getResource()->import($this);      
    }
    
    public function importUniversal()
    {
        return $this->getResource()->importUniversal($this);      
    }
    
    public function updateLinks()
    {
        return $this->getResource()->updateLinks();      
    }  
    
    public function deleteMapRow($id)
    {
        return $this->getResource()->deleteMapRow($id);      
    }
	
	public function isDeletable($id)
	{
        return $this->getResource()->isDeletable($id); 		
	
	}
	
	public function newSetterId($id)
	{
        return $this->getResource()->newSetterId($id); 		
	}
    
    public function countEmptyDropdowns()
    {
        $num = 0;
        
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();
        
        // we assume the values are always exist.
        $values = $session->getData($name);
        foreach ($values as $k=>$dropdown){
            if (is_numeric($k) && !$dropdown){
                $num++;
            } 
        } 
        
        return $num;
    }
    
    public function getDropdownsByCurrent($current)
    {
        $dropdowns = array();
        while ($current){
            $valueModel = Mage::getModel('amfinder/value')->load($current);
            $dropdowns[$valueModel->getDropdownId()]= $current;
            $current = $valueModel->getParentId();            
        }

        return $dropdowns;
    }     
    
    /**
     * For current finder creates his description for URL 
     *
     * @return string like year-make-model-number.html
     */
    public function createUrlParam()
    {
        $sep    = Mage::getStoreConfig('amfinder/general/separator');
        $suffix = Mage::getStoreConfig('amfinder/general/suffix');

        
        $urlParam = '';
        
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();
        
        $values = $session->getData($name);
        if (!is_array($values)){
            $values = array();    
        }
        
        foreach ($values as $k => $value) {
            if ('current' == $k) {
                $urlParam .= $value . $suffix;
                break;
            }
            
            if (!empty($value) && is_numeric($k)){
                $valueModel =  Mage::getModel('amfinder/value')->load($value);
                if ($valueModel->getId()){
                    $urlParam .= strtolower(preg_replace('/[^\da-zA-Z]/', '-', $valueModel->getName())) . $sep;                
                }
            }
        }
        
        return $urlParam;
    }
    
    /**
     *  Get last `number` part from the year-make-model-number.html string 
     *
     * @param string $param like year-make-model-number.html
     * @return string like number
     */
    public function parseUrlParam($param)
    {
        $sep    = Mage::getStoreConfig('amfinder/general/separator');
        $suffix = Mage::getStoreConfig('amfinder/general/suffix');
        
        $param = explode($sep, $param);
        $param = str_replace($suffix, '', $param[count($param)-1]);
        
        return $param;
                    
    }
    
    public function removeGet($url, $name, $amp = true) {
        $url = str_replace("&amp;", "&", $url); 
        list($urlPart, $qsPart) = array_pad(explode("?", $url), 2, ""); 
        parse_str($qsPart, $qsVars); 
        unset($qsVars[$name]); 
        
        if (count($qsVars) > 0) { 
            $url = $urlPart."?".http_build_query($qsVars); 
            if ($amp) 
                $url = str_replace("&", "&amp;", $url); 
        }
        else {
            $url = $urlPart;    
        } 
        return $url; 
    }    
    
    public function getCurrentCategory()
    {
         // it's ok.  last visited is the current, not previous category
         return Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
    } 

    public function getInitialCategoryId()
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();
        $value = $session->getData($name);
        
        return $value['filter_category_id'];
    }
    
    public function isAllowedInCategory()
    {
        $res = Mage::getStoreConfig('amfinder/general/category_search');
        if (!$res){
            return true;
        }
        
        if (!$this->getInitialCategoryId()){
            return true;
        }

        return ($this->getInitialCategoryId() == $this->getCurrentCategory());       
    }


	/**
	 * temporary method
	 * @return Varien_Db_Adapter_Interface
	 */
	public function getDbAdapter()
	{
		return $this->getResource()->getDbAdapter();
	}

	public function getListInvalidSku($finderId = null)
	{
		if($finderId === true) {
			$finderId = $this->getId();
		}

		return $this->getResource()->getListInvalidSku($finderId);
	}

	protected function _afterDelete()
	{

		Mage::getModel('amfinder/import')->afterDeleteFinder($this->getId());
		return parent::_afterDelete();
	}


}
