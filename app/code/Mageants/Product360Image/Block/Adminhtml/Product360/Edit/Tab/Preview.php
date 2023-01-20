<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml\Product360\Edit\Tab;

use \Magento\Backend\Block\Template\Context;

class Preview extends \Magento\Framework\View\Element\Template
{
    
     /**
      * constructor
      *
      * @param  Context $context,
      * @param  array $data
      */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
