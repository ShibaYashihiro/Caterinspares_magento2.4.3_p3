<?php
/**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;

class ErrorLog extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_errorlog';
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
		Context $context,
		LayoutFactory $resultLayoutFactory
	) 
	{
		parent::__construct($context );
		
		$this->_resultLayoutFactory = $resultLayoutFactory;
	}
	
	 /**
     * execute action
     *
     * @return layout result
     */
	public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
		
		$resultLayout->getLayout()->getBlock('partfinder.partfinders.edit.tab.hostorylog');

        return $resultLayout ;
    }
	
	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

}