<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */

namespace Mageants\Product360Image\Model\ResourceModel\Product360;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
     /**
      * ID Field Name
      *
      * @var string
      */
    public $idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Mageants\Product360Image\Model\Product360', 'Mageants\Product360Image\Model\ResourceModel\Product360');
    }
    
    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        
        $countSelect->reset(\Zend_Db_Select::GROUP);
        
        return $countSelect;
    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    public function _toOptionArray($valueField = 'id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}
