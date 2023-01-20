<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

use \Magento\Backend\App\Action\Context;
use Mageants\Product360Image\Model\ResourceModel\Image;
use \Magento\Framework\Controller\Result\RawFactory;

class UploadImage extends \Magento\Backend\App\Action
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_upload';
    /**
     * Upload model
     *
     * @var \Mageants\Product360Image\Model\Upload
     */
    public $uploadModel;

    /**
     * Image model
     *
     * @var \Mageants\Product360Image\Model\Product360\Image
     */
    public $imageModel;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    public $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        \Mageants\Product360Image\Model\Upload $uploadModel,
        Image $imageModel
    ) {
        parent::__construct($context);
        
        $this->imageModel = $imageModel;
        
        $this->uploadModel = $uploadModel;
        
        $this->resultRawFactory = $resultRawFactory;
    }
     /*
	 * Check permission via ACL resource
	 */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::RESOURCE_ID);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            $product_id = $this->getRequest()->getParam('productid');
            
            $data = [];
            
            $result = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel
                ->getBaseDir($product_id), $data);
            
            $result['url']= $this->imageModel->getProduct360Url($product_id, $result['file']);
            
            unset($result['tmp_name']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        $response = $this->resultRawFactory->create();
        
        $response->setHeader('Content-type', 'text/plain');
        
        $response->setContents(json_encode($result));
        
        return $response;
    }
}
