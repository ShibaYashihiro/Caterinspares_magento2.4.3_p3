<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Model\ResourceModel;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\Filesystem;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Store\Model\StoreManagerInterface;
        
class Image
{
    
    /**
     * @var _storeManager
     */
    public $storeManager;
    /**
     * Media sub folder
     *
     * @var string
     */
    public $subDir = 'mageants/product360image/product360';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * File system model
     *
     * @var \Magento\Framework\Filesystem
     */
    public $fileSystem;

    /**
     * @var \Magento\Framework\View\Asset\Repositoryp
     */
    public $assetRepo;

    /**
     * constructor
     *
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
        Repository $assetRepo,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        
        $this->fileSystem = $fileSystem;
        
        $this->assetRepo = $assetRepo;
        
        $this->storeManager = $storeManager;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl($product_id)
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
        .$this->subDir.'/image/'.$this->getConvertedId($product_id);
    }
    
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($product_id = '')
    {
        return $this->fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->
        getAbsolutePath($this->subDir.'/image/'.$this->getConvertedId($product_id));
    }
    
    /**
     * get product 360 images base url
     *
     * @return string
     */
    public function getProduct360Url($product_id, $image_name)
    {
        return $this->getBaseUrl($product_id) . '/'. $image_name;
    }
    /**
     * get product 360 path from media
     *
     * @return string
     */
    public function getProduct360Path($product_id, $image_name)
    {
        return $this->subDir.'/image/'.$this->getConvertedId($product_id) . '/'. $image_name;
    }
    
    /**
     * get convert product to base64_encode
     *
     * @return string
     */
    public function getConvertedId($product_id = null)
    {
        if ($product_id) {
            return base64_encode($product_id);
        } else {
            return '';
        }
    }
    /**
     * get category tree icon
     *
     * @return string
     */
    public function getSheetExampleImage()
    {
        return $this->assetRepo->getUrl("Mageants_Product360Image::images/sheetmode.jpg");
    }
}
