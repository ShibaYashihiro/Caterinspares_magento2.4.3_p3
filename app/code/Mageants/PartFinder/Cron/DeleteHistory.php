<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Cron;

use Mageants\PartFinder\Helper\Data as PartFinderHelper;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory as PartFinderImportFilesHistoryCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory as PartFinderImportFilesLogsCollectionFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;

class DeleteHistory 
{
   /**
     * Part Finder Data Helper
     * 
     * @var \Mageants\PartFinder\Helper\Data
     */
    protected $_helper;
   /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory
     */
    protected $_partFinderImportFilesHistoryCollectionFactory;

    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory
     */
    protected $_partFinderImportFilesLogsCollectionFactory;
	/**
     * DateTime
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    /**
     * constructor
     * 
     * @param PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory
     * @param PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory
     * @param PartFinderHelper $helper
     * @param DateTime $datetime
     */
    public function __construct(
        PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory,
        PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory,
		PartFinderHelper $helper,
		DateTime $datetime
    )
    {	
        $this->_partFinderImportFilesHistoryCollectionFactory = $partFinderImportFilesHistoryCollectionFactory;
        
		$this->_partFinderImportFilesLogsCollectionFactory = $partFinderImportFilesLogsCollectionFactory;

		$this->_helper = $helper;

		$this->_datetime = $datetime;		
    }
 
    public function execute() 
	{
		$history_life_time = $this->_helper->getHistoryLyfTime();
		
		$date = new \Zend_Date($this->_datetime->gmtTimestamp());
		$date->subDay($history_life_time);
		$past_date = $date->toString('Y-m-d H:i:s');
		
        $collection = $this->_collectionFactory->create();		
		$collection->addFieldToSelect(["id"]);
		$collection->addFieldToFilter("import_start_at",['lteq'=>$past_date]);
		
		$history_ids = [];
		
        foreach ($collection as $history) 
		{
			$history_ids [] = $history->getId();
            $history->delete();
        }
		
		$partFinderImportFilesLogsCollection = $this->_partFinderImportFilesLogsCollectionFactory->create();
		$connection = $partFinderImportFilesLogsCollection->getConnection();
		
		if(count($history_ids))
		{
			/*Delete log table data*/			
			$table = $partFinderImportFilesLogsCollection->getMainTable();
			$connection->delete($table,__("finder_import_history_id IN (%1)",implode(",",$history_ids)));
		}
		
        return $this;
    }
}