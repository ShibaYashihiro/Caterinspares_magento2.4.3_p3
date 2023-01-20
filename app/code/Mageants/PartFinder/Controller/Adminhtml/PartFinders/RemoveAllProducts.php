<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\PartFindersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\App\Action\Context;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;	
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;

class RemoveAllProducts extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_massdel';
    /**
     * Mass Action Filter
     * 
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinders\CollectionFactory
     */
    protected $_collectionFactory;
	/**
     * CollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionsCollection;
	/**
     *  PartFinderOptionValuesCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory
     */
    protected $_partFinderOptionValuesCollectionFactory;
	/**
     * PartFinderOptionValueMapCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory
     */
    protected $_partFinderOptionValueMapCollectionFactory;
	/**
     * constructor
     * 
     * @param PartFindersFactory $partfindersFactory,
     * @param Registry $coreRegistry
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
		PartFindersFactory $partfindersFactory,
		PartFinderOptionsCollectionFactory $partFinderOptionCollectionFactory,
		PartFinderOptionValuesCollectionFactory $partFinderOptionValueCollectionFactory,
		PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory,
		Registry $coreRegistry,
        Context $context
    )
    {
		$this->_partFinderOptionsCollection = $partFinderOptionCollectionFactory;
				
		$this->_partFinderOptionValuesCollectionFactory = $partFinderOptionValueCollectionFactory;
		
		$this->_partFinderOptionValueMapCollectionFactory = $partFinderOptionValueMapCollectionFactory;
		
        parent::__construct($partfindersFactory,$coreRegistry, $context );
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
		$resultRedirect = $this->resultRedirectFactory->create();
		
		$partfinder = $this->_initPartFinder();

		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		
		if($finder_id = $partfinder->getId())
		{
				
			$partFinderOptionsCollection = $this->_partFinderOptionsCollection->create();
			
			$finder_options_ids = $partFinderOptionsCollection->addFieldToFilter("finder_id",$partfinder->getId())->getAllIds();
			
			$partFinderOptionValuesCollectionFactory = $this->_partFinderOptionValuesCollectionFactory->create();
			
			$partFinderOptionValuesCollectionFactory->addFieldToFilter("option_id",["in"=>$finder_options_ids]);
			$value_ids = $partFinderOptionValuesCollectionFactory->getAllIds();
			
			$partFinderOptionValueMapCollectionFactory = $this->_partFinderOptionValueMapCollectionFactory->create();
			 
			$connection = $partFinderOptionValueMapCollectionFactory->getConnection();
			
			/*Delete value map table data*/			
			$table = $partFinderOptionValueMapCollectionFactory->getMainTable();
			$connection->delete($table,__("value_id IN (%1)",implode(",",$value_ids)));
			
			/*Delete value table data*/			
			$table = $partFinderOptionValuesCollectionFactory->getMainTable();
			$connection->delete($table,__("option_id IN (%1)",implode(",",$finder_options_ids)));
 			 
			$this->messageManager->addSuccess(__('All product data deleted successfully.'));
			
			$resultRedirect->setPath('*/*/edit/id/'.$finder_id);
		}
		else
		{
			$this->messageManager->addSuccess(__('Part Finder was not found.'));
			$resultRedirect->setPath('*/*/');
		}
		
		
        return $resultRedirect;
    }
}
