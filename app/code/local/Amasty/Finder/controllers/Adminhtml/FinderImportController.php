<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


class Amasty_Finder_Adminhtml_FinderImportController extends Mage_Adminhtml_Controller_Action
{
	public function massDeleteAction()
	{
		$finderId = $this->getRequest()->getParam('finder_id');
		$deleteIds = $this->getRequest()->getParam('file_ids');
		if(!is_array($deleteIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Please select file(s).'));
		} else {
			try {
				$collection = Mage::getModel('amfinder/importLog')
					->getCollection()
					->addFieldToFilter("file_id", array('in'=>$deleteIds));
				foreach($collection as $item) {
					$item->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('Files deleted successfully.'));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

		}
		$this->_redirect('adminhtml/finder/edit', array('id' => $finderId));
	}

	public function deleteAction()
	{
		$finderId = $this->getRequest()->getParam('finder_id');
		$id = $this->getRequest()->getParam('file_id');
		try {
			Mage::getModel('amfinder/importLog')->load($id)->delete();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('File deleted successfully.'));
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('adminhtml/finder/edit', array('id' => $finderId));
	}


	public function uploadAction()
	{
		$finderId = $this->getRequest()->getParam('finder_id');
		$isDeleteExistingData = $this->getRequest()->getParam('delete_existing_data');
		$newFileName = $isDeleteExistingData ? 'replace.csv' : null;

		$errors = array();
		$content = '';
		try {
			$fileName = Mage::getModel('amfinder/import')->upload('file', $finderId, $newFileName);
			$content = $this->__('The file %s has been uploaded', $fileName);
			if($fileName == 'replace.csv') {
				$content .= $this->__(', all other files in the queue have been removed');
			}
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$this->getResponse()->setBody(
			Mage::helper('core')->jsonEncode(
				array(
					'errors' => $errors,
					'content' => $content,
				)
			)
		);
	}

	public function gridAction()
	{
		$this->loadLayout();
		$id     = (int) $this->getRequest()->getParam('finder_id');
		$model  = Mage::getModel('amfinder/finder')->load($id);
		Mage::register('amfinder_finder', $model);

		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_import_processFiles_grid')->toHtml()
		);
	}


	public function gridHistoryAction()
	{
		$this->loadLayout();
		$id     = (int) $this->getRequest()->getParam('finder_id');
		$model  = Mage::getModel('amfinder/finder')->load($id);
		Mage::register('amfinder_finder', $model);

		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_tab_importHistory_grid')->toHtml()
		);
	}


	public function gridErrorsAction()
	{
		$this->loadLayout();
		$fileId     = (int) $this->getRequest()->getParam('file_id');
		$fileState     = $this->getRequest()->getParam('file_state');
		if($fileState == Amasty_Finder_Helper_Data::FILE_STATE_PROCESSING) {
			$model = 'importLog';
		} else {
			$model = 'importLogHistory';
		}
		$model  = Mage::getModel('amfinder/'.$model)->load($fileId);
		if (!$model->getId()) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Record does not exist'));
			$this->_redirect('adminhtml/finder/');
			return;
		}
		Mage::register('amfinder_importFile', $model);

		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amfinder/adminhtml_finder_edit_import_errors_grid')->toHtml()
		);
	}

	public function errorsAction()
	{
		$fileId     = (int) $this->getRequest()->getParam('file_id');
		$fileState     = $this->getRequest()->getParam('file_state');
		if($fileState == Amasty_Finder_Helper_Data::FILE_STATE_PROCESSING) {
			$model = 'importLog';
		} else {
			$model = 'importLogHistory';
		}
		$model  = Mage::getModel('amfinder/'.$model)->load($fileId);
		if (!$model->getId()) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfinder')->__('Record does not exist'));
			$this->_redirect('adminhtml/finder/');
			return;
		}
		Mage::register('amfinder_importFile', $model);
		$this->loadLayout();
		$this->renderLayout();
	}


	public function runFileAction()
	{
		$fileId = (int) $this->getRequest()->getParam('file_id');
		$fileLog = Mage::getModel('amfinder/importLog')->load($fileId);

		if(!$fileLog->getId()) {
			$json = Zend_Json::encode(
				array(
					'isCompleted'=>true,
					'message'=>$this->__('File not exists'),
					'progress'=> $fileLog->getProgress(),
				)
			);
			$this->getResponse()->setBody($json);
			return;
		}

		if($fileLog->getIsLocked()) {
			$json = Zend_Json::encode(
				array(
					'isCompleted'=>true,
					'message'=>$this->__('File already running'),
					'progress'=> $fileLog->getProgress(),
				)
			);
			$this->getResponse()->setBody($json);
			return;
		}
		$countProcessedRows = 0;
		Mage::getModel('amfinder/import')->runFile($fileLog, $countProcessedRows);

		$data = array();
		$data['isCompleted'] = (bool)$fileLog->getEndedAt();
		if($data['isCompleted']) {
			if($countProcessedRows) {
				$data['message'] = $this->__('File imported successfully');
				$data['message'] .= $this->__(' with %d errors', $fileLog->getCountErrors());
			} else {
				$data['message'] =
					$this->__('The file is invalid, please see <a href="javascript:amShowErrorsPopup(\'%s\')">errors log</a> for details.',
						$this->getUrl('*/finderImport/errors', array('file_id'=>$fileLog->getFileLogHistoryId(), 'file_state'=>Amasty_Finder_Helper_Data::FILE_STATE_ARCHIVE)));
			}


		} else {
			$data['message'] = $this->__('Imported %d rows of total %d rows (%d%%)', $fileLog->getCountProcessingLines(), $fileLog->getCountLines(), $fileLog->getProgress());
		}

		$data['progress'] = $fileLog->getProgress();

		$json = Zend_Json::encode($data);
		$this->getResponse()->setBody($json);
	}

	public function runAllAction()
	{
		Mage::getModel('amfinder/import')->runAll();
		$this->getResponse()->setBody('Complete');
	}

	public function clearHistoryAction()
	{
		Mage::getModel('amfinder/importLogHistory')->clearArchive();
		$this->getResponse()->setBody('Complete');
	}

	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('catalog/amfinder');
	}

	public function deleteHistoryAction()
	{
		$finderId = $this->getRequest()->getParam('finder_id');
		$id = $this->getRequest()->getParam('file_id');
		try {
			Mage::getModel('amfinder/importLogHistory')->load($id)->delete();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('File deleted successfully.'));
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('adminhtml/finder/edit', array('id' => $finderId));
	}

	public function deleteAllProductsAction()
	{
		$finderId = $this->getRequest()->getParam('finder_id');
		try {
			$finder = Mage::getModel('amfinder/finder')->load($finderId);
			Mage::getModel('amfinder/import')->clearOldData($finder);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfinder')->__('All products deleted successfully.'));
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('adminhtml/finder/edit', array('id' => $finderId));
	}

}