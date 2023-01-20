<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory as PartFinderImportFilesHistoryCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory as PartFinderImportFilesLogsCollectionFactory;
use \Magento\Backend\App\Action\Context;

class MassDeleteHistory extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_massdel';
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory
     */
    protected $_partFinderImportFilesLogsCollectionFactory;

    /**
     * constructor
     * 
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory,
        PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory,
        Context $context
    )
    {	
        $this->_collectionFactory = $partFinderImportFilesHistoryCollectionFactory;
        
		$this->_partFinderImportFilesLogsCollectionFactory = $partFinderImportFilesLogsCollectionFactory;
		
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
		
		$history_ids = $this->getRequest()->getPost("history_ids");
		
        $collection = $this->_collectionFactory->create();
		
		$collection->addFieldToFilter("id",['in'=>$history_ids]);
		
		$delete = 0;
		
        foreach ($collection as $history) 
		{	
            $history->delete();
			
            $delete++;
        }
		
		$partFinderImportFilesLogsCollection = $this->_partFinderImportFilesLogsCollectionFactory->create();
		
		$connection = $partFinderImportFilesLogsCollection->getConnection();
		
		/*Delete log table data*/			
		$table = $partFinderImportFilesLogsCollection->getMainTable();
		$connection->delete($table,__("finder_import_history_id IN (%1)",implode(",",$history_ids)));
		
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
		
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		
        return $resultRedirect->setPath('mageants_partfinder/partfinders/edit/id/'.$finder_id);
    }
}
