<?php
namespace Mageants\BaseProduct\Model\Import\ProductImport;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
       const ERROR_INVALID_IMAGE= 'InvalidImage';
    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init($context);
}
