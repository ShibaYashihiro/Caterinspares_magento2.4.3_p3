<?php
namespace Mageants\PartFinder\Model\Source;

use \Mageants\PartFinder\Model\PartFindersFactory;

class PartfinderOption implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        PartFindersFactory $partfindersFactory
    ) {
        $this->_partfindersFactory = $partfindersFactory;
    }

    public function toOptionArray()
    {
        $partfinder = $this->_partfindersFactory->create()->getCollection()->addFieldToFilter("status",array('eq' => 1));
        $PartfinderOptionArray = array();
        $PartfinderOptionArray['0'] = ['value' => '', 'label' => __('-- Please Select --')];
        foreach ($partfinder as $partfinderdata) {
			$PartfinderOptionArray[$partfinderdata['id']] = ['value' => $partfinderdata['id'], 'label' => __($partfinderdata['name'])];
		}
		return $PartfinderOptionArray;
    }
}