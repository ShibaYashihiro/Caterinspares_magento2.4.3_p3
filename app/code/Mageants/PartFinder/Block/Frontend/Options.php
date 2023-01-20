<?php
 /**
 * @category Mageants Advancesizechart
 * @package Mageants_Advancesizechart
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Frontend;
 
use \Magento\Framework\View\Element\Template\Context;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;
use \Mageants\PartFinder\Helper\Data as PartFinderHelper;
/**
 * Class BlockRepository
 *
 * @package Mageants\PartFinder\Block\Frontend
 */
class Options extends \Magento\Framework\View\Element\Template
{
	/**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory
     */
    protected $_partFinderOptionValuesCollectionFactory;
    /**
     * @param Context $context,
	 * @param array $data = [],
	 * @param Data $helper,
	 * @param PartFindersFactory $partfindersFactory
     */
	public function __construct(
		Context $context,
		array $data = [],
		PartFinderOptionValuesCollectionFactory $partFinderOptionValuesCollectionFactory
	) {	
		parent::__construct($context, $data);
			
		$this->_partFinderOptionValuesCollectionFactory = $partFinderOptionValuesCollectionFactory;
		
	}
	/**
     * Retrieve PartFinder Selected Option 
     *
     * @return string
     */
	public function getSelectedOption()
	{
		$lavel= $this->getRequest()->getParam("lavel");
		
		$param = $this->getRequest()->getParam(PartFinderHelper::FINDER_KEY);
		
		$selected = "";
		
		if($param  && $param != "")
		{
			$options = explode("-",$param);
			
			if(isset($options[$lavel]))
			{
				$selected = $options[$lavel];
			}
		}
		
		return $selected;
	}
	/**
     * Retrieve PartFinder Option Children
     *
     * @return getFinderOptionChlidren
     */
	public function getFinderOptionChlidren()
	{
		$option_id = $this->getRequest()->getParam("id",true);
		
		$parent_id = $this->getRequest()->getParam("parent_id",true);
		$value = $this->getRequest()->getParam("value");
		$level = $this->getRequest()->getParam("lavel");

		$partFinderOptionValuesCollection = $this->_partFinderOptionValuesCollectionFactory->create();
		if($level != 0){
			$partFinderOptionValuesCollection2 = $this->_partFinderOptionValuesCollectionFactory->create()
			->addFieldToFilter("option_id",$option_id)
			->addFieldToFilter("value",$value);
			$parent_array = [];		
			foreach ($partFinderOptionValuesCollection2 as $key => $col2) {
				$loop_parent = $col2->getParentId();
				$p_value = $level-1;
				$isCorrectValue = true;
				while($loop_parent > 0){
					$p = $this->getRequest()->getParam("p".$p_value);

					$heirarchyCheck = $this->_partFinderOptionValuesCollectionFactory->create()
					->addFieldToFilter("id",$loop_parent)
					->addFieldToFilter("value",$p)
					->getFirstItem();
					if(!empty($heirarchyCheck->getData())){
						$isCorrectValue = true;
						$loop_parent = $heirarchyCheck->getData('parent_id');
					}else{
						$isCorrectValue = false;
						break;
					}
				}
				if($isCorrectValue){
					$parent_array[] = $col2->getId();
				}
			}		
			$partFinderOptionValuesCollection->addFieldToFilter("parent_id",$parent_array);

		}else{
			$partFinderOptionValuesCollection->addFieldToFilter("parent_id",$parent_id);
		}
		
		if($parent_id==0)
		{
			$partFinderOptionValuesCollection->addFieldToFilter("option_id",$option_id);
		}
		$partFinderOptionValuesCollection->getSelect()
		->group("main_table.value"); 
		return $partFinderOptionValuesCollection;
	}
}