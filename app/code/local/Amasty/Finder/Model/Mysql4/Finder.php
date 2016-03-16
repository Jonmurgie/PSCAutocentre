<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Model_Mysql4_Finder extends Mage_Core_Model_Mysql4_Abstract
{
    const MAX_LINE   = 2000;
    const BATCH_SIZE = 1000; 
    
    public function _construct()
    {    
        $this->_init('amfinder/finder', 'finder_id');
    }
    
    public function importUniversal($finder)
    {
        $err = array();
        $db = $this->_getWriteAdapter();
        $id = intVal($finder->getId());

        if ($finder->getData('importuniversal_clear') && $id){
            $db->delete($this->getTable('amfinder/universal'), "finder_id = $id"); 
        }
        
        if (empty($_FILES['importuniversal_file']['name'])){
            return $err; //ok, no data
        }
            
        $fileName = $_FILES['importuniversal_file']['tmp_name'];
        
        //for Mac OS
        ini_set('auto_detect_line_endings', 1);
        
        //file can be very big, so we read it by small chunks
        $fp = fopen($fileName, 'r');
        if (!$fp){
            throw new Exception('Can not open file');   
        }
        
        $err = array();
        $currRow = 0;
        
        $line = true; 
        while (($line = fgetcsv($fp, self::MAX_LINE, ',', '"')) !== false) {
            $i = 0;
            
            $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/universal') . '` (finder_id, sku, pid) VALUES ';
            foreach($line as $sku){
                $sql .= '("'.$id.'","'.trim($sku, "\r\n\t' ".'"').'","0"),';
            }
            $sql = substr($sql, 0, -1);
            $db->raw_query($sql);
        
        }

        $t1 = $this->getTable('amfinder/universal');
        $t2 = $this->getTable('catalog/product');
        
        $sql = "UPDATE $t1, $t2  SET pid = entity_id WHERE $t1.sku=$t2.sku";
        $db->raw_query($sql);
                       
        return $err;         
    }
        
    public function import($finder)
    {
        $err = array();
        
        $db = $this->_getWriteAdapter();
        
        //get dropdownds iDs as array 
        $dropdowns = array();
        foreach ($finder->getDropdowns() as $d){
            $dropdowns[] = $d->getId();
            $ranges[] = $d->getRange();
        }
        $ranges[count($ranges)] = 0;   
             
        $dropNum   = count($dropdowns);        
        
        if ($finder->getData('import_clear') && $dropNum){
            $ids = join(',', $dropdowns);
            $db->delete($this->getTable('amfinder/value'), "dropdown_id IN ($ids)"); 
        }
        
        if (empty($_FILES['import_file']['name'])){
            return $err; //ok, no data
        }
            
        $fileName = $_FILES['import_file']['tmp_name'];
        
        //for Mac OS
        ini_set('auto_detect_line_endings', 1);
        
        //file can be very big, so we read it by small chunks
        $fp = fopen($fileName, 'r');
        if (!$fp){
            throw new Exception('Can not open file');   
        }
        
        $err = array();
        $currRow = 0;
        
        $line = true;
        while ($line !== false){
            
            // convert file portion to the matrix
            // validate and normalize strings
            $names      = array(); // matrix h=BATCH_SIZE, w=dropNum+1;
            $namesIndex = 0;
            
            // need to handle ranges
            $newIndex = array();
            $tempNames = array();
            
            while (($line = fgetcsv($fp, self::MAX_LINE, ',', '"')) !== false) {
                $currRow++;
                
                if (count($line) != $dropNum+1){ 
                   $err[] = 'Line #' . $currRow . ' has been skipped: expected number of columns is '.($dropNum+1);
                   continue;
                }
                
                
                for ($i = 0; $i < $dropNum+1; $i++) { 
                    
                    $line[$i] = trim($line[$i], "\r\n\t' ".'"');
                    if (!$line[$i]){
                        $err[] = 'Line #' . $currRow . ' contains empty columns. Possible error.';  
                    }                     
                    
                    $match = array();
                    if ($ranges[$i] && preg_match('/^(\d+)\-(\d+)$/', $line[$i], $match)){
                        
                        $cnt = abs($match[1] - $match[2]);
                        if ($cnt) {         
                            $startValue = min($match[1], $match[2]);                   
                            for ($k = 0; $k < $cnt + 1; $k++){
                                $names[$namesIndex + $k][$i]     = $startValue + $k;
                                $tempNames[$namesIndex + $k][$i] = $startValue + $k;
                                $newIndex[$i] =  $namesIndex + $k; 
                            }  
                        }
                        else {
                            $err[] = 'Line #' . $currRow . ' contains the same values for the range. Possible error.';  
                            $names[$namesIndex][$i] = $line[$i];
                            $newIndex[$i] = $namesIndex;  
                        }  
                        
                    } 
                    else {
                        $names[$namesIndex][$i] = $line[$i];
                        $newIndex[$i] = $namesIndex;                                                                         
                    }
    
                }
                
                // multiply rows with ranges
                $multiplierRange = 1;
                $currMultiply    = 1;
                $flagRange       = false;  
                                
                for ($i = 0; $i < $dropNum+1; $i++) {
                    if ($newIndex[$i] != $namesIndex){
                       $flagRange = true;
                       if (($newIndex[$i] - $namesIndex + 1) > 0){
                            $multiplierRange = $multiplierRange * ($newIndex[$i] - $namesIndex + 1);
                       }
                    }
                }
                
                if ($flagRange){
                     $currMultiply = $multiplierRange;
                     for ($i = 0; $i < $dropNum+1; $i++) {
                         $currMultiply = intVal($currMultiply / ($newIndex[$i] - $namesIndex + 1));  // current multiplier for the column
                         for ($l = 0; $l < $multiplierRange; $l++){
                            $index = $namesIndex + intVal(( $l % ($currMultiply * ($newIndex[$i] - $namesIndex + 1)) )  / ($currMultiply));
                            if (isset($tempNames[$index][$i])){
                                $names[$namesIndex+$l][$i] = $tempNames[$index][$i];
                            }   
                            else {
                                $names[$namesIndex+$l][$i] = $names[$index][$i];  
                            }
                         }
                     }                  
                }
                $namesIndex =  $namesIndex +  $multiplierRange;
                $tempNames  = array();
                
                // break to write processed data
                if (self::BATCH_SIZE == $namesIndex){
                    break;
                }
            } // end while read 
            
            if (!$namesIndex){
                continue;
            }
  
            // like names, but 
            // a) contains real IDs from db
            // b) has additional first column=0 as artificial parent_id for the frist dropdown
            // c) has no SKU
            // d) initilized by 0 
            $parents = array_fill(0, $namesIndex, array_fill(0, $dropNum, 0));
            
            for ($j=0; $j < $dropNum; ++$j){ // columns
                $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/value') . '` (parent_id, dropdown_id, name) VALUES ';

                $insertedData = array();
                for ($i=0; $i < $namesIndex; ++$i){ //rows
                    $key = $parents[$i][$j] . '-' . $names[$i][$j];
                    
                    if (!isset($insertedData[$key])){
                        $insertedData[$key] = $parents[$i][$j];
                        $sql .= '(' . $parents[$i][$j] . ',' 
                             . $dropdowns[$j] . ',' 
                             . "'" . addslashes($names[$i][$j]) . "'),";
                    }
                    
                }
                
                //insert current column
                $sql = substr($sql, 0, -1);
                
                //Mage::getSingleton('adminhtml/session')->addSuccess($sql);
                $db->raw_query($sql);
            
                // now we need to select just inserted data to get IDs
                // we can create long where statement or select a bit more data that we actually need.
                // we are implementing the second approach
                $affectedParents = array_keys(array_flip($insertedData));
                $key = new Zend_Db_Expr('CONCAT(parent_id, "-", name)');
                $sql = $db->select()
                    ->from($this->getTable('amfinder/value'), array($key, 'value_id'))
                    ->where('parent_id IN(?)', $affectedParents)
                    ->where('dropdown_id = ?', $dropdowns[$j])
                ;
                
                //Mage::getSingleton('adminhtml/session')->addSuccess(htmlspecialchars($sql));
                $map = $db->fetchPairs($sql);
                
                for ($i=0; $i < $namesIndex; ++$i){ //rows
                    $key = $parents[$i][$j] . '-' . $names[$i][$j];
                    if (isset($map[$key])){
                        $parents[$i][$j+1] = $map[$key];
                    }
                    else {
                        $parents[$i][$j+1] = 0;
                        throw new Exception('Invalid data: key `' . $names[$i][$j] . '` is not found. Make sure the file does not contain the same string lowercase/uppercase.');
                    }
               } 
               
            } //end columns
            
            // now insert SKU as we know the last value_id
            $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/map') . '` (value_id, sku) VALUES ';
            $insertedData  = array();
            for ($i=0; $i < $namesIndex; ++$i){ 
                $valueId = $parents[$i][$dropNum];
                $skus = explode(',', $names[$i][$dropNum]);
                foreach($skus as $sku){
                    $key = $valueId . '-' . $sku;
                    if (!isset($insertedData[$key])){
                        $insertedData[$key] = 1;
                        $sql .= '(' . $valueId . ",'" . addslashes($sku) . "'),";
                    }
                }
            }
            $sql = substr($sql, 0, -1);
            
            $db->raw_query($sql);
                       
        }// end main loop
        
        return $err;         
        
    }
    
    public function updateLinks()
    {
        $db = $this->_getWriteAdapter();
        $t1 = $this->getTable('amfinder/map');
        $t2 = $this->getTable('catalog/product');

		if (false){ // set it to true to update parent products as well
		    $relation = $this->getTable('catalog/product_relation');
			$entity = $this->getTable('catalog/product');
		
			$sql = "
			insert ignore into $t1 (pid, value_id, sku)
			select parent_id, value_id, sku

			from $relation rel

			inner join
			(SELECT entity_id, value_id
			FROM `$entity` e
			inner join
			(select value_id, sku
			from $t1) map on map.sku = e.sku)
			entities on entities.entity_id = rel.child_id

			left join $entity et on rel.parent_id = et.entity_id

			group by parent_id, value_id, sku";

			$db->raw_query($sql);
		}

        $sql = "UPDATE $t1, $t2 SET pid = entity_id WHERE $t1.sku=$t2.sku";
        $db->raw_query($sql);

        $sql = $db->select()
            ->from($t1, array('sku'))
            ->where('pid=0')
            ->limit(10);
        return $db->fetchCol($sql);
    }
    
    public function deleteMapRow($id)
    {
        $db = $this->_getWriteAdapter();
        $table = $this->getTable('amfinder/map');
        $sql = "DELETE FROM $table WHERE `id` = $id"; 
        $db->raw_query($sql);      
        return true;     
    }

	public function newSetterId($id)
    {
        $db = $this->_getWriteAdapter();
        $table = $this->getTable('amfinder/map');
        $selectSql = $db->select()
        ->from($table)
        ->where('id = ?',$id);
        $result = $db->fetchRow($selectSql) ;
        return $result['value_id'];     
    }
	
	public function isDeletable($id)
	{
        $db = $this->_getWriteAdapter();
        $table = $this->getTable('amfinder/map');
        $selectSql = $db->select()
        ->from($table)
        ->where('value_id = ?',$id);
        $result = $db->fetchRow($selectSql) ;
		//die($selectSql->__toString());
		if (isset($result['value_id']))
		{
			if ($result['value_id'])
			{
				//die("!!!");
				return false;
			}
		}
		
        $table2 = $this->getTable('amfinder/value');
        $selectSql2 = $db->select()
        ->from($table2)
        ->where('parent_id = ?',$id);
		
        $result2 = $db->fetchRow($selectSql2) ;
		if (isset($result2['value_id']))
		{
			if ($result2['value_id'])
			{
				
				return false;
			}
		}		
		 return true;
	}
    
    public function addConditionToProductCollection($collection, $valueId, $countOfEmptyDropdowns, $finderId)
    {
        $db = $this->_getWriteAdapter();
        
        $ids = array($valueId);
        for ($i = 0; $i < $countOfEmptyDropdowns; $i++){
            $selectChild = $db->select()
                ->from(array('vf'=>$collection->getTable('amfinder/value')), 'value_id')
                ->where('vf.parent_id IN (?)', $ids);
            $ids = $db->fetchCol($selectChild);
        }
        
        $select = $collection->getSelect();
        $alias = 'map_amfinder_' . $finderId;        
        
        if (Mage::getStoreConfig('amfinder/general/universal')){
            // we need sub selects
            $univProducts = $db->select()
                ->from(array('fu' => $collection->getTable('amfinder/universal')), 'pid')
                ->where('fu.finder_id = ?', $finderId); 
            
            $productIdsSelect =  $db->select()
                ->from(array('fm' => $collection->getTable('amfinder/map')), 'pid')
                ->where('fm.value_id IN (?)', $ids); 
    
            $allProducts = $db->select()
                ->union(array($univProducts, $productIdsSelect)); 
             
            $query = 'SET SESSION group_concat_max_len = 1000000';    
            $db->query($query);       
            
            $query = 'SELECT GROUP_CONCAT(pid) INTO @' . $alias . ' FROM ('.(new Zend_Db_Expr((string)$allProducts)).') as subquery;';
            $db->query($query);

            if (false === strpos((string)$select, $alias)){
                $select->where('FIND_IN_SET(e.entity_id, @'.$alias.')') ;

                if (Mage::getStoreConfigFlag('amfinder/general/universal_last')) {
                    $select->distinct()
                        ->joinLeft(
                            array('fu' => $collection->getTable('amfinder/universal')),
                            'fu.pid = e.entity_id',
                            array()
                        )
                        ->order('fu.pid ASC')
                    ;
                }
            }
        }
        else {
            // we can just join tables
            if (false === strpos((string)$select, $alias)){
                $select
                    ->joinInner(array($alias => $collection->getTable('amfinder/map')), $alias.'.pid=e.entity_id', array())
                    ->where($alias.'.value_id IN (?)', $ids);            
            }            
        }
        
        return true;        
    }

	/**
	 * temporary method
	 * @return Varien_Db_Adapter_Interface
	 */
	public function getDbAdapter()
	{
		return $this->_getWriteAdapter();
	}

	public function getListInvalidSku($finderId = null)
	{
		$db = $this->_getWriteAdapter();
		$sql = $db->select()
			->from($this->getTable('amfinder/map'), array('sku'))
			->where('pid=0')
			->limit(10);
		if(!is_null($finderId)) {
			$sql
				->joinLeft(array("amvalue"=>$this->getTable('amfinder/value')), 'am_finder_map.value_id = amvalue.value_id')
				->joinLeft(array("amdropdown"=>$this->getTable('amfinder/dropdown')), "amvalue.dropdown_id = amdropdown.dropdown_id")
				->where('amdropdown.finder_id = ?', $finderId);
		}
		return $db->fetchCol($sql);
	}
        
}