<?php
/**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Helper;

/**
 * @method string getValue()
 * @method bool getDisabled()
 * @method File setExtType(\string $extType)
 */
class Number extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * constructor
     * 
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
		
        $this->setType('number');
		
        $this->setExtType('number');
    }
	 /**
     * Add Extra attributes
     *
     * @return array
     */
	public function getHtmlAttributes()
	{
		$attrs = parent::getHtmlAttributes();
		
		$attrs[] = "min";
		$attrs[] = "max";
		
		return $attrs;
	}


}