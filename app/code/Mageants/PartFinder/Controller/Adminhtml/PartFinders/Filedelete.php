<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\PartFinder\Model\PartFinderImportFilesFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory as PartFinderImportFilesHistoryCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory as PartFinderImportFilesLogsCollectionFactory;

use \Mageants\PartFinder\Model\ResourceModel\Files;
use \Magento\Backend\App\Action\Context;

class Filedelete extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	 const RESOURCE_ID = 'Mageants_PartFinder::partfinders_filedelete';
	  /**
     * Image model
     * 
     * @var \Mageants\PartFinder\Model\Product360\Image
     */
     protected $_filesModel;
	 /**
     * PartFinderImportFilesFactory model
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
     protected $_jsonFactory;
	 /**
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory
     */
     protected $_partFinderImportFiles;
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory
     */
    protected $_partFinderImportFilesLogsCollectionFactory;	
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory
     */
    protected $_partFinderImportFilesHistoryCollectionFactory;
	  /**
     * constructor
     * 
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
		JsonFactory $jsonFactory,
		PartFinderImportFilesFactory $PartFinderImportFiles,
		PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory,
        PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory,
		Files $filesModel,
		Context $context
    )
    {
	    $this->_jsonFactory = $jsonFactory;
		
		$this->_filesModel = $filesModel;
		
		$this->_partFinderImportFiles = $PartFinderImportFiles;
		
		$this->_partFinderImportFilesLogsCollectionFactory = $partFinderImportFilesLogsCollectionFactory;
		
		$this->_partFinderImportFilesHistoryCollectionFactory = $partFinderImportFilesHistoryCollectionFactory;
		
        parent::__construct($context);
    }
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {		
		$result['success'] = false;
		
        $id = $this->getRequest()->getParam('id');
		
        $finder_id = $this->getRequest()->getParam('finder_id');
		
		if ($id) 
		{
            $filepath = "";
			
            try 
			{
                /** @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory $partfinder_file */
                $partfinder_file = $this->_partFinderImportFiles->create();
				
                $partfinder_file->load($id);
				
				$base_path = $this->_filesModel->getBaseDir($finder_id);
				
                $filepath = $base_path . DIRECTORY_SEPARATOR . $partfinder_file->getFileName();
				
                $partfinder_file->delete();
				
				$partFinderImportFilesLogsCollection = $this->_partFinderImportFilesLogsCollectionFactory->create();
				$partFinderImportFilesHistoryCollection = $this->_partFinderImportFilesHistoryCollectionFactory->create();
				
				$connection = $partFinderImportFilesLogsCollection->getConnection();
				
				/*Delete log table data*/			
				$log_table = $partFinderImportFilesLogsCollection->getMainTable();
				$connection->delete($log_table,__("import_file_id IN (%1)",$id));
				
				/*Delete history table data*/			
				$history_table = $partFinderImportFilesHistoryCollection->getMainTable();
				$connection->delete($history_table,__("import_file_id IN (%1)",$id));
				
                $this->messageManager->addSuccess(__('The csv file has been deleted.'));
				
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_partfinder_partfinder_on_filedelete',
                    ['filepath' => $filepath, 'status' => 'success']
                );
				
				if(file_exists($filepath)) unlink($filepath);	
				
				$result['message'] = __('The csv file has been deleted.');
                $result['success'] = true;
            } 
			catch (\Exception $e) 
			{
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_partfinder_partfinder_on_filedelete',
                    ['filepath' => $filepath, 'status' => 'fail']
                );
				
                // display error message
                $result['message'] = $e->getMessage();
                
            }
        }
		else{
			$result['message'] =__('Something wrong! Please try after sometime.');
		}
		
        $jsonResultFactory = $this->_jsonFactory->create();
		
		return $jsonResultFactory->setData($result);
    }
	
	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
}
