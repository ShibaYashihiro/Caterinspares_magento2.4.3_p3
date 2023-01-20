<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;
        
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterfac
     */
    public $scopeConfig;
    
    /**
     * @var array
     */
    public $defaultConfig;
    
    /**
     * @var \Magento\Backend\Helper\Data
     */
    public $helperBackend;

    /*Extention Enable Disable Constant*/
    const ENABLE = 'mageants_product360image/general/enable';
    
     /**
      *  construct
      *
      * @param Context $context,
      * @param ScopeConfigInterface $scopeConfig
      * @param Data $HelperBackend
      */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $HelperBackend
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getscopeConfig();
        $this->helperBackend = $HelperBackend;
        $this->defaultConfig['setting']['framesX'] = '';
        $this->defaultConfig['setting']['frames'] = '';
    }
    
    /**
     * Retrieve extention enable or disable
     *
     * @return boolean
     */
    public function isExtentionEnable()
    {
        return $this->scopeConfig->getValue(self::ENABLE, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Retrieve default Product360 configuration
     *
     * @return array
     */
    public function getDefaultSetting()
    {
            return $this->defaultConfig;
    }
        
    /**
     * Retrieve edited Product360 preview url
     *
     * @return string
     */
    public function getProduct360PreviewTabUrl()
    {
         return $this->helperBackend
         ->getUrl('mageants_product360image/product360/preview/ajax/1/', ['_current' => true]);
    }
        
    /**
     * Retrieve serialize setting
     *
     * @return array
     */
    public function serializeSetting($setting)
    {
         return serialize($setting);
    }
        
    /**
     * Retrieve unserialize setting
     *
     * @return array
     */
    public function unserializeSetting($setting)
    {
        $data['setting'] = [];
        
        if (!empty($setting)) {
            return unserialize($setting);
        } else {
            return $data;
        }
    }
}
