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
use \Magento\Framework\View\Result\LayoutFactory;

class History extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_edit_history';
	/**
	 * 
	 * @var  \Magento\Framework\View\Result\LayoutFactory
     */
	protected $_resultLayoutFactory = null;
	
	  /**
     * constructor
     * 
	 * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
	public function __construct(
		PartFindersFactory $partfindersFactory,
		Context $context,
		Registry $coreRegistry,
		LayoutFactory $resultLayoutFactory
	) 
	{
		parent::__construct($partfindersFactory,$coreRegistry, $context );
		
		$this->_resultLayoutFactory = $resultLayoutFactory;
	}
	
	 /**
     * execute action
     *
     * @return layout result
     */
	public function execute()
    {
		
		$partfinder = $this->_initPartFinder();
		
        $resultLayout = $this->_resultLayoutFactory->create();
		
		$resultLayout->getLayout()->getBlock('partfinder.partfinders.edit.tab.history');

        return $resultLayout;
    }
	
	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

}