<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Framework\Stdlib\DateTime\DateTime;
        
class Product360 extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $date
     * @param string|null $resourcePrefix
     */
    
    public function __construct(
        Context $context,
        DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        
        $this->date = $date;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mageants_product360image', 'id');
    }
    
     /**
      * Process post data before saving
      *
      * @param \Magento\Framework\Model\AbstractModel $object
      * @return $this
      * @throws \Magento\Framework\Exception\LocalizedException
      */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt($this->date->gmtDate());
        }

        $object->setUpdatedAt($this->date->gmtDate());

        return parent::_beforeSave($object);
    }
}
