<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\PartFinder\Model\PartFinderImportFilesFactory;
use \Mageants\PartFinder\Model\Upload;
use \Mageants\PartFinder\Model\ResourceModel\Files;
use \Magento\Framework\Stdlib\DateTime\DateTime;

class Csvimport extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_csv_import';
	  /**
     * PartFinderImportFilesFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory
     */
    protected $_partFinderImportFiles;
	/**
     * Upload model
     * 
     * @var \Mageants\PartFinder\Model\Upload
     */
    protected $_uploadModel;
    /**
     * File model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\Files
     */
    protected $_filesModel;
    /**
     * Image model
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    /**
     * constructor
     * 
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
		JsonFactory $jsonFactory,
		Upload $uploadModel,
		PartFinderImportFilesFactory $PartFinderImportFiles,
		Files $filesModel,
		DateTime $datetime,
        Context $context
    )
    {
	    $this->_jsonFactory = $jsonFactory;
		
		$this->_filesModel = $filesModel;
		
		$this->_uploadModel = $uploadModel;
		
		$this->_datetime = $datetime;
		
		$this->_partFinderImportFiles = $PartFinderImportFiles;
		
        parent::__construct($context);
    }

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
		$result = ['success' =>false];
		
		 try {
			
			$finder_id = $this->getRequest()->getParam('finder_id');
            
			$data = array();
			
			$result = $this->_uploadModel->uploadFileAndGetName('qqfile', $this->_filesModel->getBaseDir($finder_id), $data);
			
			$result['url']= $this->_filesModel->getCsvUrl($finder_id, $result['file']);
			
			$importFileModel = $this->_partFinderImportFiles->create();
			
			$csv_path = $result['path'] . DIRECTORY_SEPARATOR . $result['file'];
			
			$csv = file($csv_path,FILE_SKIP_EMPTY_LINES);
			
			$datetime = $this->_datetime->gmtDate("Y-m-d H:i:s");
			
			$data = [
				'finder_id' => $finder_id,
				'file_name' => $result['file'],
				'processed_rows' => 0,
				'total_row' => count($csv) - 1,
				'status' => 1,
				'is_locked' => 0,
				'created_at' => $datetime,
				'updated_at' => $datetime
			];
			
			$importFileModel->setData($data);
			
			$importFileModel->save();
			
			unset($result['tmp_name']); 
			
			$result['success'] = true;
			
			$result['message'] = __("Successfully import file uploaded.");
        } 
		catch (\Exception $e) 
		{
            $result['message'] = $e->getMessage();
        }

		$jsonResultFactory = $this->_jsonFactory->create();
		
		return $jsonResultFactory->setData($result);
    }
}
