<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
 
namespace Mageants\PartFinder\Model\Core\Catalog\ResourceModel;

use Mageants\PartFinder\Helper\Data as PartFinderHelper;

class Category extends \Magento\Catalog\Model\ResourceModel\Category
{	
    /**
     * Get products count in category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getProductCount($category)
    {
			//Get Object Manager Instance
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		//Load product by product id
		$partfinder_product_helper = $objectManager->get('Mageants\PartFinder\Helper\PartFinderProducts');
		
		$product_ids = $partfinder_product_helper->getProductIds();
				
		if(count($product_ids))
		{
			$productTable = $this->_resource->getTableName('catalog_category_product');

			$select = $this->getConnection()->select()->from(
				['main_table' => $productTable],
				[new \Zend_Db_Expr('COUNT(main_table.product_id)')]
			)->where(
				'main_table.category_id = :category_id'				
			)->where(
				'main_table.product_id IN ('. implode(",",$product_ids) .')'
			);

			$bind ['category_id'] = (int)$category->getId();
			
			$counts = $this->getConnection()->fetchOne($select, $bind);
			
			return intval($counts);
		}
		
		return parent::getProductCount($category);
    }
}
