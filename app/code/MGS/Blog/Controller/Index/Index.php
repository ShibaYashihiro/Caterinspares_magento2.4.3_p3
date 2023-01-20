<?php

namespace MGS\Blog\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $blogHelper;
    protected $resultForwardFactory;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Blog\Helper\Data $blogHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->blogHelper = $blogHelper;
        $this->resultForwardFactory = $resultForwardFactory;
         $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->blogHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPageFactory = $this->resultPageFactory->create();
        if ($this->blogHelper->getConfig('general_settings/template')) {
            $resultPageFactory->getConfig()->setPageLayout($this->blogHelper->getConfig('general_settings/template'));
        }
        // Add page title
        

            // Add page title
        $resultPageFactory->getConfig()->getTitle()->set(__('Blog'));

        
        return $resultPageFactory;
    }
}
