<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{	
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_grid';
    /**
     * Result Page 
     * 
     */
    protected $_resultPage = null;
	
    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
	protected $_resultPageFactory = null;
	
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) 
	{
		parent::__construct($context);
		
		$this->_resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		//Call page factory to render layout and page content
		$this->_setPageData();
		
        return $this->getResultPage();
	}

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
	/*
	 * return  result page
	 */
    public function getResultPage()
    {
        if (is_null($this->_resultPage)) 
		{
            $this->_resultPage = $this->_resultPageFactory->create();
        }
		
        return $this->_resultPage;
    }
	
	/*
	 * set page data and active menu 
	 *
	 * return $this 
	 */
    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
		
        $resultPage->setActiveMenu('Mageants_PartFinder::partfinders');
		
        $resultPage->getConfig()->getTitle()->prepend((__('Part Finders')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('Mageants'), __('Mageants'));
		
        $resultPage->addBreadcrumb(__(' Part Finder'), __('Manage Part Finders'));

        return $this;
    }


}