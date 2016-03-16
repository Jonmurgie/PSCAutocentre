<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Model_Import
{
	const CONFIG_MAX_LIMIT_IN_PART = 'amasty/import/limit';
	const MAX_ERRORS_IN_FILE = 1000;



	public function runFile($fileLog, &$countProcessedRows)
	{
		$fileName = $fileLog->getFileName();
		$finderId = $fileLog->getFinderId();

		$filePath = $fileLog->getFilePath();

		if($fileLog->getIsLocked() == 1){
			return 0;
		}

		if($fileLog->getStatus() == Amasty_Finder_Model_ImportLog::STATE_UPLOADED) {
			$fileLog->setStartedAt(date('Y-m-d H:i:s'));
		}

		if($fileLog->getLastStartProcessingLine() != 0 && $fileLog->getLastStartProcessingLine() == $fileLog->getCountProcessingLines()) {
			$this->getErrorLog()->error($fileLog->getId(), 0, 'Error! File is executing the second time');
			$fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
			return 0;
		}

		if(!is_file($filePath)) {
			$this->getErrorLog()->error($fileLog->getId(), 0, 'File not exists');
			$fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
			return 0;
		}

		$fp = fopen($filePath, 'r');

		if (!$fp){
			$this->getErrorLog()->error($fileLog->getId(), 0, 'Can not open file');
			$fileLog->error()->setEndedAt(date('Y-m-d H:i:s'))->save()->archive()->delete();
			return 0;
		}



		$fileLog->setIsLocked(1);
		$fileLog->setLastStartProcessingLine($fileLog->getCountProcessingLines());
		if($fileLog->getStatus() == Amasty_Finder_Model_ImportLog::STATE_UPLOADED) {
			$countLines = $this->countLines($fp);
			$fileLog->setCountLines($countLines);
			$fileLog->setStatus(Amasty_Finder_Model_ImportLog::STATE_PROCESSING);
		}
		$fileLog->save();

		/**
		 * @var Amasty_Finder_Model_Finder
		 */
		$finder = Mage::getModel('amfinder/finder')->load($finderId);

		if(!$finder->getId()) {
			$this->getErrorLog()->error($fileLog->getId(), 0, 'Finder id '.$finderId.' does not exists');
			$fileLog->setIsLocked(0)->error()->save();
			return 0;
		}

		if($fileLog->getCountProcessingLines() == 0 && $fileName == 'replace.csv') {
			$this->clearOldData($finder);
		}



		$countProcessedRowsInCurrentFile = $fileLog->getCountProcessingRows();
		$countProcessedLinesInCurrentFile = $fileLog->getCountProcessingLines();
		for($i=1; $i <=$countProcessedLinesInCurrentFile; $i++) {
			fgets($fp);
		}


		//get dropdownds iDs as array
		$dropdowns = array();
		foreach ($finder->getDropdowns() as $d){
			$dropdowns[] = $d->getId();
			$ranges[] = $d->getRange();
		}
		$ranges[count($ranges)] = 0;

		$countDropDowns   = count($dropdowns);


		// convert file portion to the matrix
		// validate and normalize strings
		$names      = array(); // matrix h=BATCH_SIZE, w=dropNum+1;
		$namesIndex = 0;

		// need to handle ranges
		$newIndex = array();
		$tempNames = array();

		while (($line = fgetcsv($fp, Amasty_Finder_Model_Mysql4_Finder::MAX_LINE, ',', '"')) !== false && $countProcessedRows < Mage::helper('amfinder')->getMaxRowsPerImport()) {
			$countProcessedLinesInCurrentFile++;
			if (count($line) != $countDropDowns+1){
				$this->getErrorLog()->error(
					$fileLog->getId(),
					$countProcessedLinesInCurrentFile,
					'Line #' . $countProcessedLinesInCurrentFile . ' has been skipped: expected number of columns is '.($countDropDowns+1)
				);
				$fileLog->error();
				continue;
			}

			$cnt = array();
			for ($i = 0; $i < $countDropDowns+1; $i++) {
				$line[$i] = trim($line[$i], "\r\n\t' ".'"');
				if (!$line[$i]){
					$this->getErrorLog()->error(
						$fileLog->getId(),
						$countProcessedLinesInCurrentFile,
						'Line #' . $countProcessedLinesInCurrentFile . ' contains empty columns. Possible error.'
					);
					$fileLog->error();
				}

				$match = array();
				if ($ranges[$i] && preg_match('/^(\d+)\-(\d+)$/', $line[$i], $match)){
					$cnt[$i] = abs($match[1] - $match[2]);
				}
			}

			$cntRange = 1;
			foreach($cnt as $count) {
				if($count) {
					$cntRange *= $count;
				}
			}

			if($cntRange >= Mage::helper('amfinder')->getMaxRowsPerImport()) {
				$this->getErrorLog()->error($fileLog->getId(), $countProcessedLinesInCurrentFile, 'Line #' . $countProcessedLinesInCurrentFile . ' contains big range!');
				$fileLog->error();
				continue;
			}

			///// ***************** START old import code ************************ ////
			for ($i = 0; $i < $countDropDowns+1; $i++) {

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
						$this->getErrorLog()->error(
							$fileLog->getId(),
							$countProcessedLinesInCurrentFile,
							'Line #' . $countProcessedLinesInCurrentFile . ' contains the same values for the range. Possible error.'
						);
						$fileLog->error();
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
			$flagRange       = false;

			for ($i = 0; $i < $countDropDowns+1; $i++) {
				if ($newIndex[$i] != $namesIndex){
					$flagRange = true;
					if (($newIndex[$i] - $namesIndex + 1) > 0){
						$multiplierRange = $multiplierRange * ($newIndex[$i] - $namesIndex + 1);
					}
				}
			}

			if ($flagRange){
				$currMultiply = $multiplierRange;
				for ($i = 0; $i < $countDropDowns+1; $i++) {
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

			$countProcessedRowsInCurrentFile += $multiplierRange;
			$countProcessedRows += $multiplierRange;
			///// *****************  END old import code ************************ ////
		}
		///// ***************** START old import code ************************ ////
		$fileLog->setCountProcessingRows($countProcessedRowsInCurrentFile);
		$fileLog->setCountProcessingLines($countProcessedLinesInCurrentFile);

		if (!$namesIndex){
			////$fileLog->setIsLocked(0)->save();
			if($line === false) {
				$fileLog->setEndedAt(date('Y-m-d H:i:s'))->archive()->delete();
			} else {
				$fileLog->setIsLocked(0)->save();
			}
			return 0;
		}





		// like names, but
		// a) contains real IDs from db
		// b) has additional first column=0 as artificial parent_id for the frist dropdown
		// c) has no SKU
		// d) initilized by 0
		$parents = array_fill(0, $namesIndex, array_fill(0, $countDropDowns, 0));
		$db = $finder->getDbAdapter();

		for ($j=0; $j < $countDropDowns; ++$j){ // columns
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
					//throw new Exception('Invalid data: key `' . $names[$i][$j] . '` is not found. Make sure the file does not contain the same string lowercase/uppercase.');

					$this->getErrorLog()->error(
						$fileLog->getId(),
						$countProcessedLinesInCurrentFile,
						'Invalid data: key `' . $names[$i][$j] . '` is not found. Make sure the file does not contain the same string lowercase/uppercase.'
					);
					$fileLog->error();


					if($line === false) {
						$fileLog->setEndedAt(date('Y-m-d H:i:s'))->archive()->delete();
					} else {
						$fileLog->setIsLocked(0)->save();
					}
					return;
				}
			}
		} //end columns

		// now insert SKU as we know the last value_id
		$sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/map') . '` (value_id, sku) VALUES ';
		$insertedData  = array();
		for ($i=0; $i < $namesIndex; ++$i){
			$valueId = $parents[$i][$countDropDowns];
			$skus = explode(',', $names[$i][$countDropDowns]);
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

		///// *****************  END old import code ************************ ////
		$finder->updateLinks();





		if($line === false) {
			$fileLog->setEndedAt(date('Y-m-d H:i:s'))->archive()->delete();
		} else {
			$fileLog->setIsLocked(0)->save();
		}

		return $countProcessedRows;
	}



	public function runAll()
	{
		$dir = Mage::helper('amfinder')->getFtpImportDir();

		$dirHandle = opendir($dir);
		$finderIds = array();
		while (false !== ($childrenDir = readdir($dirHandle))) {
			if(!is_dir($dir.$childrenDir) || intval($childrenDir) != $childrenDir) {
				continue;
			}
			$finderIds[] = $childrenDir;
			//$this->loadNewFilesFromFtp($childrenDir);
		}
		closedir($dirHandle);

		/**
		 * @var $collectionFinder Amasty_Finder_Model_Mysql4_Finder_Collection
		 */
		$collectionFinder = Mage::getModel('amfinder/finder')->getCollection();
		$collectionFinder->addFieldToFilter('finder_id', array('in'=>$finderIds));
		foreach ($collectionFinder as $finder) {
			$this->loadNewFilesFromFtp($finder->getId());
		}


		/**
		 * @var $collection Amasty_Finder_Model_Mysql4_ImportLog_Collection
		 */
		$collection = Mage::getModel('amfinder/importLog')->getCollection();
		$collection
			->addFieldToFilter('is_locked', 0)
			->orderForImport();

		$countProcessedRows = 0;
		foreach($collection as $fileLog) {
			$this->runFile($fileLog,$countProcessedRows);
			if($countProcessedRows >= Mage::helper('amfinder')->getMaxRowsPerImport()){
				break;
			}
		}


	}

	public function getLog($fileName, $finderId)
	{
		return Mage::getModel('amfinder/importLog')->loadByNameAndFinder($fileName, $finderId);
	}

	/**
	 * @return Amasty_Finder_Model_ImportLogErrors
	 */
	public function getErrorLog()
	{
		return Mage::getModel('amfinder/importLogErrors');
	}




	public function loadNewFilesFromFtp($finderId)
	{
		$dir = Mage::helper('amfinder')->getFtpImportDir().$finderId."/";
		if(!is_dir($dir)){
			return;
		}
		$hasDeleteAllFiles = false;
		$dirHandle = opendir($dir);
		while (false !== ($file = readdir($dirHandle))) {
			if(is_file($dir.$file) && $file != '..' && $file != '.') {
				Mage::getModel('amfinder/importLog')->addUniqueFile($file, $finderId);
				if($file == 'replace.csv') {
					$hasDeleteAllFiles = true;
				}
			}
		}
		closedir($dirHandle);

		if($hasDeleteAllFiles) {
			Mage::getModel('amfinder/importLog')
				->getCollection()
				->addFieldToFilter('finder_id', $finderId)
				->addFieldToFilter('file_name', array('neq'=>'replace.csv'))
				->walk('delete');
		}
	}



	public function upload($fileField, $finderId, $fileName = null)
	{
		$dir = Mage::helper('amfinder')->getFtpImportDir().$finderId."/";
		$uploader = new Varien_File_Uploader($fileField);

		$uploader->setAllowedExtensions(array('csv'));

		$fileName = !is_null($fileName) ? $fileName : $_FILES[$fileField]['name'];
		$fileName = $uploader->getCorrectFileName($fileName);

		if(Mage::getModel('amfinder/importLog')->hasIssetReplaceFile($finderId)) {
			Mage::throwException(Mage::helper('amfinder')->__('Upload is impossible, there is a file replace.csv'));
		}

		if(is_file($dir.$fileName)) {
			Mage::throwException(Mage::helper('amfinder')->__('The file with the same name already exists! '.$fileName));
		}

		$result = $uploader->save($dir,$fileName);

		if(!$result) {
			Mage::throwException(Mage::helper('amfinder')->__('Error occurred save file'));
		}

		$fileName = $uploader->getUploadedFileName();
		$this->loadNewFilesFromFtp($finderId);
		return $fileName;
	}


	/**
	 * @param Amasty_Finder_Model_Finder $finder
	 */
	public function clearOldData($finder)
	{
		$ids = array();
		foreach($finder->getDropdowns() as $dropdown) {
			$ids[] = $dropdown->getId();
		}

		Mage::getResourceModel('amfinder/value')->deleteValuesByDropDowns($ids);
	}

	public function countLines($fileHandle)
	{
		$i = 0;
		while(fgets($fileHandle) !== false){
			$i++;
		}

		rewind($fileHandle);

		return $i;
	}


	public function getTable($tableName)
	{
		return Mage::getResourceModel('amfinder/importLog')->getTable($tableName);
	}


	public function afterDeleteFinder($finderId)
	{
		/**
		 * @var $collection Mage_Core_Model_Mysql4_Collection_Abstract
		 */
		$collection = Mage::getModel('amfinder/importLog')->getCollection()->addFieldToFilter('finder_id', $finderId);
		foreach($collection as $item) {
			$item->delete();
		}
		$collectionHistory = Mage::getModel('amfinder/importLogHistory')->getCollection()->addFieldToFilter('finder_id', $finderId);
		foreach($collectionHistory as $item) {
			$item->delete();
		}
	}
}