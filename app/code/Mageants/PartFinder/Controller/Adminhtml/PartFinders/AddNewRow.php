<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\PartFinder\Model\PartFindersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;

class AddNewRow extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_new';
	 /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

   /**
     * constructor
     * 
     * @param Session $backendSession
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param PartFindersFactory $partfindersFactory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        PartFindersFactory $partfindersFactory,
        Registry $registry,
        Context $context
    )
    {
        $this->_backendSession = $context->getSession();
		
        $this->_resultPageFactory = $resultPageFactory;
		
        parent::__construct($partfindersFactory, $registry, $context);
    }

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	/**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
		/** @var \Mageants\PartFinder\Model\PartFinders $partfinder */
        $partfinder = $this->_initPartFinder();
		
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
		
        $resultPage->setActiveMenu('Mageants_PartFinder::partfinders');
		
        $resultPage->getConfig()->getTitle()->set(__('Part Finder Add New Product'));
		
        $title = __('Part Finder Add New Product');
		
        $resultPage->getConfig()->getTitle()->prepend($title);
		
        $data = $this->_backendSession->getData('mageants_partfinder_partfinder_new_row_data', true);
		
        if (!empty($data)) 
		{
            $partfinder->setData($data);
        }
		
        return $resultPage;
    }

}