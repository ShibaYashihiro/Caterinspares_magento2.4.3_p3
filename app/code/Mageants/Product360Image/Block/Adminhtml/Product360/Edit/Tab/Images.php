<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml\Product360\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use \Magento\Backend\Block\Template\Context;
use \Mageants\Product360Image\Helper\Data;
use \Magento\Backend\Block\Media\Uploader;
use \Magento\Framework\View\Element\AbstractBlock;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Json\EncoderInterface;
use \Magento\Catalog\Model\Product\Media\Config;
use \Magento\Framework\Registry;
use \Mageants\Product360Image\Model\ResourceModel\Image;
use Magento\Backend\Block\DataProviders\ImageUploadConfig as ImageUploadConfigDataProvider;

class Images extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Default Helper options
     *
     */
    public $helper;
    
    /**
     * @var Mageants\Product360Image\Model\ResourceModel\Image
     */
    public $imageModel;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    public $jsonEncoder;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var ImageUploadConfigDataProvider
     */
    private $imageUploadConfigDataProvider;

    /**
     * constructor
     *
     * @param  Context $context,
     * @param  Data $helper,
     * @param  array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        Image $imageModel,
        Registry $coreRegistry,
        ImageUploadConfigDataProvider $imageUploadConfigDataProvider = null,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        
        $this->imageModel = $imageModel;
        
        $this->helper      = $helper;
        
        $this->coreRegistry = $coreRegistry;
        
        $this->imageUploadConfigDataProvider = $imageUploadConfigDataProvider
            ?: ObjectManager::getInstance()->get(ImageUploadConfigDataProvider::class);
        
        parent::__construct($context, $data);
    }
     
    /**
     * @return AbstractBlock
     */
    public function _prepareLayout()
    {
        $this->addChild(
            'uploader',
            \Magento\Backend\Block\Media\Uploader::class,
            ['image_upload_config_data' => $this->imageUploadConfigDataProvider]
        );
        
        $productid = $this->getRequest()->getParam('productid');
        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('mageants_product360image/product360/uploadimage/productid/'.$productid)
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.jpg, .jpeg, .png)'),
                    'files' => ['*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        return parent::_prepareLayout();
    }
    
    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }
    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }
    
    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }
    
    /**
     * @return string
     */
    public function getImagesJson()
    {
        $product_id = $this->getRequest()->getParam('productid');
        
        $product360 = $this->coreRegistry->registry('mageants_product360image');
        
        $imagesData = $this->helper->unserializeSetting($product360->getImages());
        
        $value = $imagesData;

        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            !empty($value['images'])
        ) {
            $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            
            $images = $this->sortImagesByPosition($value['images']);
            
            foreach ($images as &$image) {
                $image['url'] = $this->imageModel->getProduct360Url($product_id, $image['file']);
                
                $fileHandler = $directory->stat($this->imageModel->getProduct360Path($product_id, $image['file']));
                
                $image['size'] = $fileHandler['size'];
            }
            
            return $this->jsonEncoder->encode($images);
        }
        
        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }
    
    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        
        $imageTypes['product360'] = [
            'code' => 'product360',
            'value' => '',
            'label' => 'Product360 Images',
            'name' => 'product360',
        ];
    
        return $imageTypes;
    }

    /**
     * Retrieve JSON data
     *
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->jsonEncoder->encode($this->getImageTypes());
    }
    /**
     * Prepare Product360 for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('360 Images');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
