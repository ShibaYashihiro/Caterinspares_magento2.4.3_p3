<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
 
namespace Mageants\Product360Image\Model;
 
class Product360 extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Model Initialization
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        
        $this->_init('Mageants\Product360Image\Model\ResourceModel\Product360');
    }
    
    /**
     *
     * @return default values for edit
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
