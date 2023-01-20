<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Block;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Mageants\Product360Image\Model\Product360Factory;
use \Mageants\Product360Image\Helper\Data;

class Product360 extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mageants\Product360Image\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Interceptor
     */
    public $currentProduct = false;

    /**
     * @var \Mageants\Product360Image\Model\Product360Factory
     */
    public $currentProduct360 = false;
    
    /**
     * @var \Mageants\Product360Image\Model\Product360Factory
     */
    public $product360setting = false;

    /**
     * @var \Mageants\Product360Image\Model\Product360Factory
     */
    public $product360images = false;

     /**
      * constructor
      *
      * @param  Context $context,
      * @param  array $data
      */
    public function __construct(
        Data $helper,
        Product360Factory $product360Factory,
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->helper = $helper;
        
        $this->_coreRegistry = $coreRegistry;
        
        $this->currentProduct = $this->_coreRegistry->registry('current_product');
        
        if ($this->currentProduct && $this->currentProduct->getId()) {
            $product360Collection = $product360Factory->create()->getCollection();

            $product360Collection->addFieldToFilter('product_id', $this->currentProduct->getId());

            
            if (!empty($product360Collection->getData())) {
            
                $this->currentProduct360 = $product360Collection->getFirstItem();
                
                $setting = $helper->unserializeSetting($this->currentProduct360->getSetting());
                
                $this->product360setting = $setting['setting'];
                
                $images = $helper->unserializeSetting($this->currentProduct360->getImages());
                

                $this->product360images = $images['images'];
            }
        }
    }
    
    /**
     * hasProduct360View
     *
     * Product360 Enable / Disable for current product
     *
     * return boolean
     */
    public function hasProduct360View()
    {
        if ($this->helper->isExtentionEnable() && $this->currentProduct360) {
            return true;
        }
        
        return false;
    }
    
    /**
     * hasFullscreenEnable
     *
     * Product360 Fullscreen Enable / Disable for current product
     *
     * return boolean
     */
    public function hasFullscreenEnable()
    {
        return $this->product360setting['enable_fullscreen'];
    }
    
    /**
     * hasEaseBlurEnable
     *
     * Product360 Ease and Blur Enable / Disable for current product
     *
     * return boolean
     */
    private function hasEaseBlurEnable()
    {
        return $this->product360setting['enable_ease_blur'];
    }
    
    /**
     * isSingleImageMode
     *
     * Product360 Single Image Enable / Disable for current product
     *
     * return boolean
     */
    public function isSingleImageMode()
    {
        return $this->product360setting['images_mode'];
    }
    
    /**
     * getSourceImage
     *
     * Product360 images
     *
     * return array / string
     */
    private function getSourceImage()
    {
        $source = [];
        if (!empty($this->product360images)) {
            if ($this->isSingleImageMode()) {
                foreach (array_reverse($this->product360images) as $image) {
                    $source[] = $image['url'];
                }
            } else {
                foreach (array_reverse($this->product360images) as $image) {
                    $source[] = $image['url'];
                }
            }
            if (count($source) == 1 && !$this->isSingleImageMode()) {
                $source = array_merge($source, $source);
            }
            return array_reverse($source);
        } else {
            return $source;
        }
    }
    
    /**
     * hasProduct360View
     *
     * Product360 Fullscreen Json config for 360 view
     *
     * return string
     */
    public function getProduct360Json()
    {
        $json= $this->product360setting;
        
        $json['source'] = $this->getSourceImage();
        
        if ($this->hasEaseBlurEnable()) {
            $json['mods'][] = '360';
            $json['mods'][] = 'ease';
            $json['mods'][] = 'blur';
            $json['mods'][] = 'drag';
        }
        
        return  json_encode($json);
    }

    public function getProduct360Images()
    {
        $json= $this->product360setting;
        
        $images = $this->getSourceImage();
        
        return $images;
    }
}
