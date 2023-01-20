<?php
 /**
 * @category Mageants Advancesizechart
 * @package Mageants_Advancesizechart
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Frontend\Core;

use Mageants\PartFinder\Helper\Data as PartFinderHelper;

/**
 * Class BlockRepository
 *
 * @package Mageants\PartFinder\Block\Frontend
 */
class SearchResult extends \Magento\CatalogSearch\Block\Result
{
    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
		$finder_param = $this->getRequest()->getParam(PartFinderHelper::FINDER_KEY);

		if($finder_param!="")
		{
			$parts = explode("-",$finder_param);
			
			$part_text = [];
			
			end($parts);
			$last_key = key($parts);
			
			foreach($parts as $key=>$part)
			{
				if($key == $last_key) continue;
				
				$part_text[] = ucfirst($part);
			}
			
			$query = implode(" -> ",$part_text);
		}
		else
		{
			$query = $this->catalogSearchData->getEscapedQueryText();
			
		}
		// var_dump('here'.$query );exit;
		
        return __("Search results for: '%1'", $query);;
    }
}