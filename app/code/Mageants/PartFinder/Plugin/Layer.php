<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
 
 namespace Mageants\PartFinder\Plugin;

use Magento\Framework\App\RequestInterface;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;

class Layer
{
    /**
     * @param \Magento\Catalog\Model\Layer $subject,
     * @param \Closure $proceed
     */
    public function aroundGetProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        \Closure $proceed
    ) {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;

        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $result = $proceed();
        
        $isEnable = $scopeConfig->getValue("mageants_partfinder/general/enable", $storeScope);
        if ($isEnable) {
            $page_limit = 12;
            if ($request->getParam('product_list_limit')) {
                $page_limit = $request->getParam('product_list_limit');
            }
            if ($request->getParam('findpart')) {
                //Load product by product id
                $partfinder_product_helper = $objectManager->get('Mageants\PartFinder\Helper\PartFinderProducts');
                
                $product_ids = $partfinder_product_helper->getProductIds();
                if (count($product_ids)) {
                    $result->getSelect()->where("e.entity_id IN (?)", $product_ids)->limit($page_limit);
                }
                if (count($result) == 0 && count($product_ids)) {
                    $result = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $product_ids))
                    ->setPageSize($page_limit);
                }
            }
        }
        return $result;
    }
}
