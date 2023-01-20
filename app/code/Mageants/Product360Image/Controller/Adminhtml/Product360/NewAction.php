<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

use \Magento\Backend\Model\View\Result\ForwardFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\Product360Image\Model\Upload;
use \Mageants\Product360Image\Model\ResourceModel\Image;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_new_edit';
    /**
     * Redirect result factory
     *
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public $resultForwardFactory;

    /**
     * constructor
     *
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     */
    public function __construct(
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        
        parent::__construct($context);
    }
    /*
	 * Check permission via ACL resource
	 */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::RESOURCE_ID);
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        
        $resultForward->forward('edit');
        
        return $resultForward;
    }
}
