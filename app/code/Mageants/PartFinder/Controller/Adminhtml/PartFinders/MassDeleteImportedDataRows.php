<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory;
use \Magento\Backend\App\Action\Context;

class MassDeleteImportedDataRows extends \Magento\Backend\App\Action
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
     * PartFinderOptionValues Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptionValuesFactory
     */
    protected $_partFinderOptionValuesFactory;

    /**
     * constructor
     * 
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory,
        PartFinderOptionValuesFactory $partFinderOptionValuesFactory,
        Context $context
    )
    {	
        $this->_collectionFactory = $partFinderOptionValueMapCollectionFactory;
        $this->_partFinderOptionValuesFactory = $partFinderOptionValuesFactory;
        
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
		
		$value_map_ids = $this->getRequest()->getPost("value_map_ids");
		
        $collection = $this->_collectionFactory->create();

        $partFinderOptionValues = $this->_partFinderOptionValuesFactory->create();
		
		$collection->addFieldToFilter("id",['in'=>$value_map_ids]);
		
		$delete = 0;
		
        foreach ($collection as $history) 
		{	
            $valueId = $history->getValueId();
            $childCollection = $this->_collectionFactory->create()->addFieldToFilter('value_id', $valueId);
            if (count($childCollection) == 1) 
            {
                $parentID = $partFinderOptionValues->load($valueId)->getParentId();
                $parentCollection = $partFinderOptionValues->getCollection()->addFieldToFilter('parent_id', $parentID);
                $parentCount = count($parentCollection);
                //$partFinderOptionValues->load($valueId)->delete();

                for ($parentID; $parentID != 0;) {
                    if($parentCount == 1){
                        $nextParentID = $partFinderOptionValues->load($parentID)->getParentId();
                        $parentCollection = null;
                        $parentCollection = $partFinderOptionValues->getCollection()->addFieldToFilter('parent_id', $nextParentID);
                        $parentCount = count($parentCollection);
                        $partFinderOptionValues->load($parentID)->delete();
                        $parentID = $nextParentID;
                    }
                    else{
                        $parentID = 0;
                    }
                }
                $partFinderOptionValues->load($valueId)->delete();
            }

            $history->delete();
			
            $delete++;
        }
		
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
		
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		
        return $resultRedirect->setPath('mageants_partfinder/partfinders/edit/id/'.$finder_id);
    }
}
