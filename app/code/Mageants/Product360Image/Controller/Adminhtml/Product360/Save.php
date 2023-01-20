<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author    Mageants Team <info@mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

use \Mageants\Product360Image\Model\Product360Factory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\Product360Image\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Mageants\Product360Image\Model\ResourceModel\Image;
        
class Save extends \Mageants\Product360Image\Controller\Adminhtml\Product360
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_save';
    /**
     * Upload model
     *
     * @var \Mageants\Product360Image\Model\SlidesFactory
     */
    public $product360Factory;
    
    /**
     * @var Mageants\Product360Image\Model\ResourceModel\Image
     */
    public $imageModel;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    public $backendSession;
    
    /**
     * Banner Data Helper
     *
     * @var \Magento\Backend\Model\Session
     */
    public $bannerHelper;

    /**
     * constructor
     *
     * @param Upload $uploadModel
     * @param File $fileModel
     * @param Image $imageModel
     * @param Session $backendSession
     * @param Product360Factory $product360Factory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        product360Factory $product360Factory,
        Registry $registry,
        Image $imageModel,
        Context $context,
        Data $bannerHelper
    ) {
        $this->backendSession = $context->getSession();
        $this->bannerHelper = $bannerHelper;
        $this->imageModel = $imageModel;
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
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        
        $product360setting = $this->getRequest()->getPost('product360setting');
        $product360images = $this->getRequest()->getParam('product360images');
        $product_id = $product360setting['setting']['product_id'];
        if ($product360images['images']) {
            foreach ($product360images['images'] as $key => $image) {
                if ($image['removed'] == 1) {
                    $directory = $this->_objectManager
                    ->get('Magento\Framework\Filesystem')->getDirectoryWrite(DirectoryList::MEDIA);
                    $filePath = $this->imageModel->getProduct360Path($product_id, $image['file']);
                    $directory->delete($filePath);
                    unset($product360images['images'][$key]);
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        
          $product360 = $this->_initProduct360Image();
            
            $data['setting'] =   $this->bannerHelper->serializeSetting($product360setting);
            
        if (isset($product360setting['setting']['id'])) {
            $data['id'] = $product360setting['setting']['id'];
        }
            
            $data['product_id'] = $product_id;
            
            $data['images'] = $this->bannerHelper->serializeSetting($product360images);
            
            $product360->setData($data);
            
            $this->_eventManager->dispatch(
                'mageants_product360image_product360_prepare_save',
                [
                    'product360' => $product360,
                    'request' => $this->getRequest()
                ]
            );
            
        try {
            $product360->save();
                
            $this->messageManager->addSuccess(__('The Product360 has been saved.'));
                
            $this->backendSession->setMageantsProduct360ImageData(false);
                
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath(
                    'mageants_product360image/*/edit',
                    [
                        'id' => $product360->getId(),
                        'productid' => $product_id,
                        '_current' => true
                    ]
                );
                    
                return $resultRedirect;
            }
                
            $resultRedirect->setPath('catalog/product/index/');
                
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Product360.'));
        }
        $this->_getSession()->setMageantsProduct360ImagePostData($data);
        
        $resultRedirect->setPath(
            'mageants_product360image/*/edit',
            [
                'id' => $product360->getId(),
                'productid' => $product_id,
                '_current' => true
            ]
        );
        
        return $resultRedirect;
    }
}
