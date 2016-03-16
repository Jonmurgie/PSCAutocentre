<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Model_Mysql4_Value extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amfinder/value', 'value_id');
    }
    
    public function saveAfterEdit($newId, $id,$sku)
    {
        $db = $this->_getWriteAdapter();
        $table = $this->getTable('amfinder/map');
        $sql = "UPDATE $table SET `sku`='$sku' WHERE `value_id` = '$id' AND `id`='$newId'";
        $db->raw_query($sql);
        return Mage::getModel('amfinder/finder')->updateLinks();
        
          
    }
    
    public function getSkuById($newId, $id)
    {
        $db = $this->_getWriteAdapter();
        $selectSql = $db->select()
        ->from($this->getTable('amfinder/map'))
        ->where('value_id = ?',$id)
		->where('id = ?',$newId);
        $result = $db->fetchRow($selectSql) ;
        return $result['sku'];    
    }
    
    public function saveNewFinder($data)
    {
        $db = $this->_getWriteAdapter();
        $insertData = array();
        $parentId = 0;
        foreach ($data as $element => $value)
        {
            if (substr($element, 0, 6) == 'label_')
            {
                $insertData[] = array('dropdown_id' => substr($element,6), 'name' => $value);               
            }

        }

        foreach ($insertData as $key => $row) {
            $name[$key]  = $row['name'];
            $dropdown_id[$key] = $row['dropdown_id'];
        }
        array_multisort($dropdown_id, SORT_ASC, $name, SORT_ASC, $insertData);

        foreach ($insertData as $insertElement){

            $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/value') . "` (parent_id, dropdown_id, name) VALUES ('"
            .$parentId."','".$insertElement['dropdown_id']."','".$insertElement['name']."')"; 
            $db->raw_query($sql);
            $selectSql = $db->select()
            ->from($this->getTable('amfinder/value'))
            ->where('dropdown_id =?',$insertElement['dropdown_id'])
            ->where('parent_id =?',$parentId)
            ->where('name =?',$insertElement['name']);
            $result = $db->fetchRow($selectSql) ;

            $parentId =  $result['value_id'];                         
        }
        
        $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/map') . "` (`value_id`, `sku`) VALUES ('"
        .$parentId."','".$data['sku']."')";
        
        $db->raw_query($sql);         
        Mage::getModel('amfinder/finder')->updateLinks();
        $dropdown = Mage::getModel('amfinder/dropdown')->load($insertElement['dropdown_id']);
        $finderId = $dropdown->getFinderId();
         
        return $finderId;
        
    }

	public function deleteValuesByDropDowns($dropDownIds)
	{
		$db = $this->_getWriteAdapter();
		$ids = implode(',', $dropDownIds);
		$db->delete($this->getMainTable(), "dropdown_id IN ($ids)");
	}
}