<?php
 /**
  * @category  Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_preview';
    /**
     *
     * @var  \Magento\Framework\View\Result\LayoutFactory
     */
    public $resultLayoutFactory = null;
    
      /**
       * constructor
       *
       * @param Context $context
       * @param LayoutFactory $resultLayoutFactory
       */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        
        $this->resultLayoutFactory = $resultLayoutFactory;
    }
    
     /**
      * execute action
      *
      * @return layout result
      */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        
        $resultLayout->getLayout()->getBlock('product360image.product360.edit.tab.preview');

        return $resultLayout;
    }
    
     /*
	 * Check permission via ACL resource
	 */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::RESOURCE_ID);
    }
}
