<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Ui\Component\MassAction\Filter;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFiles\CollectionFactory as PartFinderImportFilesCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory as PartFinderImportFilesHistoryCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory as PartFinderImportFilesLogsCollectionFactory;
use \Magento\Backend\App\Action\Context;

class MassDeleteImportFiles extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_massdel';
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFiles\CollectionFactory
     */
    protected $_collectionFactory;
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
        Filter $filter,
        PartFinderImportFilesCollectionFactory $partFinderImportFilesCollectionFactory,
        PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory,
        PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory,
        Context $context
    )
    {
        $this->_filter            = $filter;
		
        $this->_collectionFactory = $partFinderImportFilesCollectionFactory;
        
		$this->_partFinderImportFilesLogsCollectionFactory = $partFinderImportFilesLogsCollectionFactory;
		
		$this->_partFinderImportFilesHistoryCollectionFactory = $partFinderImportFilesHistoryCollectionFactory;
		
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
		$finder_id = $this->getRequest()->getParam("id");
		
		$file_ids = $this->getRequest()->getPost("file_ids");
		
        $collection = $this->_collectionFactory->create();
		
		$collection->addFieldToFilter("id",['in'=>$file_ids]);
		
		$delete = 0;
		
        foreach ($collection as $import_file) 
		{	
            $import_file->delete();
			
            $delete++;
        }
		
		$partFinderImportFilesLogsCollection = $this->_partFinderImportFilesLogsCollectionFactory->create();
		$partFinderImportFilesHistoryCollection = $this->_partFinderImportFilesHistoryCollectionFactory->create();
		
		$connection = $partFinderImportFilesLogsCollection->getConnection();
		
		/*Delete log table data*/			
		$log_table = $partFinderImportFilesLogsCollection->getMainTable();
		$connection->delete($log_table,__("import_file_id IN (%1)",implode(",",$file_ids)));
		
		/*Delete history table data*/			
		$history_table = $partFinderImportFilesHistoryCollection->getMainTable();
		$connection->delete($history_table,__("import_file_id IN (%1)",implode(",",$file_ids)));
		
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
		
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		
        return $resultRedirect->setPath('mageants_partfinder/partfinders/edit/id/'.$finder_id);
    }
}
