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
class FinderTemplates implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Status values
     */
    const STATUS_HORIZONTAL = 3;
    const STATUS_VERTICAL = 2;
    const STATUS_RESPONSIVE = 1;
    const STATUS_DEFAULT = 0;

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
            ['value' => self::STATUS_HORIZONTAL,  'label' => __('Horizontal')],
            ['value' => self::STATUS_VERTICAL,  'label' => __('Vertical')]
        ];
    }
}
