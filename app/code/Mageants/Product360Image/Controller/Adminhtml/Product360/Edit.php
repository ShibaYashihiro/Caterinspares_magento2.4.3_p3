<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

use \Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\Product360Image\Model\Product360Factory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;

class Edit extends \Mageants\Product360Image\Controller\Adminhtml\Product360
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_new_edit';
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    public $backendSession;

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * constructor
     *
     * @param Session $backendSession
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param ProductFactory $productFactory
     * @param Product360Factory $product360Factory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        ProductFactory $productFactory,
        Product360Factory $product360Factory,
        Registry $registry,
        Context $context
    ) {
        $this->backendSession    = $context->getSession();
        
        $this->resultPageFactory = $resultPageFactory;
        
        $this->resultJsonFactory = $resultJsonFactory;
        
        $this->_productFactory = $productFactory;
        
        parent::__construct($product360Factory, $registry, $context);
    }

    /*
	 * Check permission via ACL resource
	 */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::RESOURCE_ID);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        $product_id = $this->getRequest()->getParam('productid');
      
        /** @var \Mageants\Product360Image\Model\Product360 $product360 */
        $product360 = $this->_initProduct360Image();
        
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('Mageants_Product360Image::product360');
        
        $resultPage->getConfig()->getTitle()->set(__('Product 360 image'));
        
        if ($id) {
            $product360->load($id);
             
            if (!$product360->getId()) {
                $this->messageManager->addError(__('This Product360 no longer exists.'));
                
                $resultRedirect = $this->resultRedirectFactory->create();
                
                $resultRedirect->setPath(
                    'mageants_product360image/*/edit',
                    [
                        'id' => $product360->getId(),
                        '_current' => true
                    ]
                );
                
                return $resultRedirect;
            }
        }
        
        if (!$product_id) {
            $product_id = $product360->getProductId();
        }
        
        $productFactory = $this->_productFactory->create();

        $product = $productFactory->load($product_id);
        
        $title = $product360->getId() ? "Edit Product360 - ". $product->getName() :
         __('New Product360 - '.$product->getName());
        
        $resultPage->getConfig()->getTitle()->prepend($title);
        
        $data = $this->backendSession->getData('mageants_product360image_product360_data', true);
        
        if (!empty($data)) {
            $product360->setData($data);
        }
        
        return $resultPage;
    }
}
