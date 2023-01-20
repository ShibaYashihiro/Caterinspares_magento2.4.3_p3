<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */

namespace Mageants\PartFinder\Model\Source;

/**
 * Class Status
 * @package Mageants\PartFinder\Model\Source
 */
class ButtonDisplay implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Status values
     */
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 0;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = ['' => ' '];
        
		foreach ($this->toOptionArray() as $option) 
		{
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
            ['value' => self::STATUS_ENABLE,  'label' => __('Any one option choose')],
            ['value' => self::STATUS_DISABLE,  'label' => __('All the option choose')],
        ];
    }
}
