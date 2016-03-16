<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */



class Amasty_Finder_Model_ImportLog extends Amasty_Finder_Model_Import_ImportLogAbstract
{
	const STATE_UPLOADED = 0;
	const STATE_PROCESSING = 1;

	const FILE_STATE = 'processing';
	//const STATE_
	public function _construct()
	{
		$this->_init('amfinder/importLog');
	}


	public function loadByNameAndFinder($fileName, $finderId)
	{
		return $this->getCollection()->addFieldToFilter('file_name', $fileName)->addFieldToFilter('finder_id', $finderId)->getFirstItem();
	}

	protected function _beforeSave()
	{
		$this->setUpdatedAt(date('Y-m-d H:i:s'));
		return parent::_beforeSave();
	}


	public function addUniqueFile($fileName, $finderId)
	{
		$this->getResource()->addUniqueFile($fileName, $finderId);
	}

	public function getState()
	{
		$_hlp = Mage::helper('amfinder');
		if($this->getStatus() == self::STATE_UPLOADED) {
			$state = $_hlp->__('Uploaded');
		} else {
			$state = $_hlp->__('Processing');
			$state .= " ".$this->getCountProcessingLines() . " " . $_hlp->__('lines of') . " " . $this->getCountLines() . ".";
			if($this->getCountErrors() > 0) {

				$state .= " " . $this->getCountErrors() . " " . $_hlp->__('errors') . ".";
			}

		}
		return $state;
	}

	public function isProcessing()
	{
		return $this->getStatus() == self::STATE_PROCESSING;
	}

	protected function _afterDelete()
	{
		$filePath = $this->getFilePath();
		if(is_file($filePath)) {
			unlink($filePath);
		}
		return parent::_afterDelete();
	}

	public function getFileState()
	{
		return Amasty_Finder_Helper_Data::FILE_STATE_PROCESSING;
	}

	public function getFieldInErrorLog()
	{
		return 'import_file_log_id';
	}


	public function archive()
	{
		$data = $this->getData();
		$data['file_id'] = null;
		$fileLogHistory = Mage::getModel('amfinder/importLogHistory');
		$fileLogHistory->setData($data);
		$fileLogHistory->save();
		$this->setFileLogHistoryId($fileLogHistory->getId());
		Mage::getModel('amfinder/importLogErrors')->archiveErrorHistory($this->getId(), $fileLogHistory->getId());


		$filePath = $this->getFilePath();
		$newFilePath = Mage::helper('amfinder')->getFtpImportDir().'archive/'.$fileLogHistory->getId().".csv";
		if(is_file($filePath)) {
			rename($filePath, $newFilePath);
		}

		return $this;
	}

	public function getFilePath()
	{
		return Mage::helper('amfinder')->getFtpImportDir().$this->getFinderId().'/'.$this->getFileName();
	}

	public function getProgress()
	{
		return ($this->getCountLines()) ? floor($this->getCountProcessingLines()/$this->getCountLines() * 100) : 100;
	}

	public function error()
	{
		$this->setCountErrors($this->getCountErrors()+1);
		return $this;
	}

	public function getMode()
	{
		if($this->getFileName() == 'replace.csv') {
			return Mage::helper('amfinder')->__('Replace Products');
		}
		return Mage::helper('amfinder')->__('Add Products');
	}


	public function hasIssetReplaceFile($finderId)
	{
		return $this->getResource()->hasIssetReplaceFile($finderId);
	}


}