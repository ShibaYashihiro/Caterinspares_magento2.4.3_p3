<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml;

use \Mageants\Product360Image\Model\Product360Factory;
use \Magento\Framework\Registry;
use \Magento\Backend\App\Action\Context;

abstract class Product360 extends \Magento\Backend\App\Action
{
    /**
     * Product360 Factory
     *
     * @var \Mageants\Product360Image\Model\Product360Factory
     */
    public $product360Factory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    public $resultRedirectFactory;

    /**
     * constructor
     *
     * @param SlidesFactory $product360Factory
     * @param Registry $coreRegistry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        Product360Factory $product360Factory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->product360Factory           = $product360Factory;
        
        $this->coreRegistry          = $coreRegistry;
        
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        
        parent::__construct($context);
    }

    /**
     * Init Post
     *
     * @return \Mageants\Product360Image\Model\Product360
     */
    public function _initProduct360Image()
    {
        $id  = (int) $this->getRequest()->getParam('id');
        
        /** @var \Mageants\Product360Image\Model\Product360 $product360 */
        
        $product360    = $this->product360Factory->create();
        
        if ($id) {
            $product360->load($id);
        }
        
        $this->coreRegistry->register('mageants_product360image', $product360);
        
        return $product360;
    }
}
