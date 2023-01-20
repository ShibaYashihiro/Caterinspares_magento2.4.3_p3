<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author    Mageants Team <info@mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml\Product360;

use \Magento\Framework\Registry;
use \Magento\Backend\Block\Widget\Context;
        
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $_urlBuilder;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        
        parent::__construct($context, $data);
    }

    /**
     * Initialize Product360 edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        
        $this->_blockGroup = 'Mageants_Product360Image';
        
        $this->_controller = 'adminhtml_product360';
        
        parent::_construct();
        
        $this->buttonList->update('save', 'label', __('Save Product360'));
        
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->remove('back');
        
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'class' => 'back',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'onclick' => 'alert()',
                            'target' => '#edit_form'
                        ]
                    ]
                ],
                'onclick' => "setLocation('". $this->_urlBuilder->getUrl('catalog/product/index')."')"
            ],
            -1
        );
        
        $this->buttonList->update('delete', 'product360', __('Delete Product360'));
    }
    
    /**
     * Retrieve text for header element depending on loaded product360
     *
     * @return string
     */
    public function getHeaderText()
    {
        $product360 = $this->coreRegistry->registry('mageants_product360image');
        
        if ($product360->getId()) {
            return __("Edit Product360 '%1'", $this->escapeHtml($product360->getProductName()));
        }
        
        return __('New Product360');
    }
}
