<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Mageants\PartFinder\Model\PartFindersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\App\Action\Context;

class Edit extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_new_edit';
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
     * Redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;
    /**
     * constructor
     * 
     * @param PageFactory $resultPageFactory
     * @param PartFindersFactory $partfindersFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        PartFindersFactory $partfindersFactory,
        Registry $registry,
        Context $context
    )
    {
        $this->_backendSession = $context->getSession();
		
        $this->_resultPageFactory = $resultPageFactory;
		
        $this->_resultRedirectFactory = $resultRedirectFactory;
		
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
        $id = $this->getRequest()->getParam('id');
		
        /** @var \Mageants\PartFinder\Model\PartFindera $partfinder */
        $partfinder = $this->_initPartFinder();
		
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
		
        $resultPage->setActiveMenu('Mageants_PartFinder::partfinders');
		
        $resultPage->getConfig()->getTitle()->set(__(' Part Finder'));
		
        if ($id) 
		{
            $partfinder->load($id);
			
            if (!$partfinder->getId()) 
			{
                $this->messageManager->addError(__('This Part Finder no longer exists.'));
				
                $resultRedirect = $this->_resultRedirectFactory->create();
				
                $resultRedirect->setPath(
                    'mageants_partfinder/*/edit',
                    [
                        'id' => $partfinder->getId(),
                        '_current' => true
                    ]
                );
				
                return $resultRedirect;
            }
        }
		
        $title = $partfinder->getId() ? $partfinder->getName() : __('New Part Finder');
		
        $resultPage->getConfig()->getTitle()->prepend($title);
		
        $data = $this->_backendSession->getData('mageants_partfinder_partfinder_data', true);
		
        if (!empty($data)) 
		{
            $partfinder->setData($data);
        }
		
        return $resultPage;
    }
}
