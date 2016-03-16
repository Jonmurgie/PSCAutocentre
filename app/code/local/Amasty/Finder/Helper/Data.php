<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */ 
class Amasty_Finder_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SORT_STRING_ASC  = 0;
    const SORT_STRING_DESC = 1;
    const SORT_NUM_ASC     = 2;
    const SORT_NUM_DESC    = 3;


	const FTP_IMPORT_DIR = '/media/amfinder/ftp_import/';

	const FILE_STATE_PROCESSING = 'processing';
	const FILE_STATE_ARCHIVE = 'archive';

    public function formatUrl($url){

        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $securedFlag = true;
        } else {
            $securedFlag = false;
        }

        $fpcEnabled = Mage::helper('core')->isModuleEnabled('Enterprise_Pagecache');

        if ($fpcEnabled)
            $url.= strpos($url, '?')?'&no_cache=1':'?no_cache=1';

        if ($securedFlag)
            $url = str_replace("http://", "https://", $url);


        return Mage::helper('core')->urlEncode($url);
    }

	public function getFtpImportDir()
	{
		return Mage::getBaseDir().self::FTP_IMPORT_DIR;
	}


	public function getArchiveLifetime()
	{
		return Mage::getStoreConfig('amfinder/import/archive_lifetime');
	}

	public function getMaxRowsPerImport()
	{

		return Mage::getStoreConfig('amfinder/import/max_rows_per_import');
	}
}