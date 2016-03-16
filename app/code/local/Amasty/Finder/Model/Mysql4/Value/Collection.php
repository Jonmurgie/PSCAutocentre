<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Model_Mysql4_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfinder/value');
    }
    
    public function joinAllFor($finder)
    {
        $select = $this->getSelect();
        $select->reset(Zend_Db_Select::FROM);
        $select->reset(Zend_Db_Select::COLUMNS);
        
        $i=0;
        foreach ($finder->getDropdowns() as $d) {
            $i = $d->getPos();
            
            $table  = array("d$i" => $this->getTable('amfinder/value'));
            $fields = array("name$i" => "d$i.name");
            if (0 == $i) {
                $select->from($table, $fields);
                $select->where("d$i.dropdown_id=" . $d->getId() );
            }
            else {
                $bind = "d$i.parent_id = d".($i-1).".value_id";
                $select->joinInner($table, $bind, $fields);
            }
            
        }
        
        $select->joinInner(
            array('m'=>$this->getTable('amfinder/map')), 
            "d$i.value_id = m.value_id",
            array('sku', 'val'=> 'm.value_id', 'vid'=>'m.id' )
        );
        
        
        
        return $this;
        
    }     
    
}