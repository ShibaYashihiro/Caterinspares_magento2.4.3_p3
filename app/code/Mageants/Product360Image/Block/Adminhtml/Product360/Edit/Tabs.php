<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml\Product360\Edit;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        
        $this->setId('product360_tabs');
        
        $this->setDestElementId('edit_form');
        
        $this->setTitle(__('Product360 Information'));
    }
}
