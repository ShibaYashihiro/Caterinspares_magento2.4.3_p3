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
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValueMapFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory;

class DeleteRow extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_delrow';    

	/**
     * PartFinderOptionValueMapFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptionValueMapFactory
     */
    protected $_partFinderOptionValueMapFactory;

	
    /**
     * PartFinderOptionValues Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptionValuesFactory
     */
    protected $_partFinderOptionValuesFactory;

	/**
     * constructor
     * 
     * @param PartFindersFactory $partfindersFactory,
     * @param Registry $coreRegistry
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
		PartFinderOptionValueMapFactory $partFinderOptionValueMapFactory,
		PartFinderOptionValuesFactory $partFinderOptionValuesFactory,
		\Mageants\PartFinder\Helper\Data $data,
		JsonFactory $jsonFactory,
        Context $context
    )
    {
		$this->_jsonFactory = $jsonFactory;
		$this->_partFinderOptionValueMapFactory = $partFinderOptionValueMapFactory;
		$this->_partFinderOptionValuesFactory = $partFinderOptionValuesFactory;
		$this->_data = $data;
		
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
		
		$val_map_id = $this->getRequest()->getParam('val_map_id');
		
		if($val_map_id)
		{
			try
			{		
				$partFinderOptionValueMapFactory = $this->_partFinderOptionValueMapFactory->create();
				$partFinderOptionValues = $this->_partFinderOptionValuesFactory->create();
				$collection[] = array();
				$parentCollection[] = array();
				$valueId = $partFinderOptionValueMapFactory->load($val_map_id)->getValueId();
				$collection = $partFinderOptionValueMapFactory->getCollection()->addFieldToFilter('value_id', $valueId);
				if (count($collection) == 1) 
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

				$partFinderOptionValueMapFactory->load($val_map_id)->delete();
				$result = ['success' => true,"message"=> __('Successfully row delete by delete row.')];							
			}
			catch(Excception $e)
			{	
				$result ['message'] =__('Sorry! Not able to delete row.');
			}
		}
		else
		{
			$result['message'] = __('Sorry! Not able to delete row.');
		}
        
		$jsonResultFactory = $this->_jsonFactory->create();
		
		return $jsonResultFactory->setData($result);
    }
}
