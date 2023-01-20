<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */

namespace Mageants\Product360Image\Model\Source;

/**
 * Class Status
 * @package Mageants\Product360Image\Model\Source
 */
class ImageMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Status values
     */
    const MODE_DEFAULT = 0;
    const MODE_SHEET = 1;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = ['' => ' '];
        
        foreach ($this->toOptionArray() as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_DEFAULT,  'label' => __('Multi Image Mode')],
            ['value' => self::MODE_SHEET,  'label' => __('Single Image Mode')],
        ];
    }
}
