<?php

namespace MGS\Blog\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Sorting extends \Magento\Framework\App\Action\Action
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
        die('hello');
    }
}
