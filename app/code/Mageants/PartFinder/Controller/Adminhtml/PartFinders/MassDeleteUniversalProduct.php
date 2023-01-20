<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory as PartFinderUniversalProductsFactory;
use \Magento\Backend\App\Action\Context;

class MassDeleteUniversalProduct extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_massdelete_universalproduct';
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * constructor
     * 
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        PartFinderUniversalProductsFactory $partFinderUniversalProductsFactory,
        Context $context
    )
    {
        $this->_collectionFactory = $partFinderUniversalProductsFactory;
		
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
		
		$product_ids = $this->getRequest()->getPost("procuct_ids");
		
		$collection = $this->_collectionFactory->create();
		
		$collection->addFieldToFilter("finder_id",$finder_id);
		
		$collection->addFieldToFilter("product_id",['in'=>$product_ids]);
		
        $delete = 0;
		
        foreach ($collection as $uniprod) 
		{
            $universal_product_ids[] = $uniprod->getId();
			
            $uniprod->delete();
			
            $delete++;
        }
		
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
		
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		
		$finder_id = $this->getRequest()->getParam("id");
		
        return $resultRedirect->setPath('mageants_partfinder/partfinders/edit/id/'.$finder_id);
    }
}
