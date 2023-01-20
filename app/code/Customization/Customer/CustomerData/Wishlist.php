<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Customization\Customer\Wishlist\CustomerData;


class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode(); 
        $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode); 
        $currencySymbol = $currency->getCurrencySymbol(); 
        $product = $wishlistItem->getProduct();

        return [
            'image' => $this->getImageData($this->itemResolver->getFinalProduct($wishlistItem)),
            'product_sku' => $product->getSku(),
            'product_id' => $product->getId(),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $currencySymbol.$product->getPriceInfo()->getPrice('regular_price')->getValue(),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem),
        ];
    }
}