<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml;

class Product360 extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_product360';
        
        $this->_blockGroup = 'Mageants_Product360Image';
        
        $this->_headerText = __('Product360');
        
        $this->_addButtonLabel = __('Add New Product360');
        
        parent::_construct();
    }
}
