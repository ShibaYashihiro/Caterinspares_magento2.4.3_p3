<?php

/**
 * Olegnax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_ProductSlider
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductSlider\Block;

use Magento\Catalog\Model\CategoryFactory;

class ProductsByCategory extends AbstractShortcode
{

    protected $_atributtes = [
        'title' => '',
        'title_align' => 'center',
        'title_tag' => 'strong',
        'title_side_line' => false,
        'products_count' => 6,
        'category_ids' => '',
        'columns_desktop' => 4,
        'columns_desktop_small' => 3,
        'columns_tablet' => 2,
        'columns_mobile' => 1,
        'loop' => false,
        'arrows' => false,
        'dots' => false,
        'nav_position' => 'left-right',
        'dots_align' => 'left',
        'show_title' => true,
        'autoplay' => false,
        'autoplay_time' => '5000',
        'pause_on_hover' => false,
        'show_addtocart' => true,
        'show_wishlist' => true,
        'show_compare' => true,
        'show_review' => true,
        'rewind' => false,
        'sort_order' => '',
    ];

    public function getProductCollection()
    {
        $categoryIds = $this->getCategoryIds();
        $categoryIds = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
        $categoryIds = array_map('intval', $categoryIds);
        $categoryIds = array_filter($categoryIds);
        $categoryIds = array_unique($categoryIds);

        $collection = parent::getProductCollection();
        if (1 == count($categoryIds)) {
            $categoryId = array_shift($categoryIds);
            $category = $this->_loadObject(CategoryFactory::class)->create()->load($categoryId);
            $collection->addCategoryFilter($category);
        } else {
            $collection->addCategoriesFilter(['eq' => $categoryIds]);
        }
        $productsCount = $this->getProductsCount();
        if ($productsCount) {
            $collection->setPageSize($productsCount);
        }
        $this->addAttributeToSort($collection);

        $collection->distinct(true);

        return $collection;
    }

    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo([
            'OLEGNAX_PRODUCTSLIDER_CATEGORY_PRODUCTS_LIST_WIDGET',
            $this->getCategoryIds()
        ]);
    }

}
