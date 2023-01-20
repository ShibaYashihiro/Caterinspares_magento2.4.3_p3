<?php

namespace Customization\Customer\Block\Product\View;


class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{

    
    public function getGalleryImagesJson()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');//get current product
        $productRepositoryFactory = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterfaceFactory');
        $product_id = $product->getId();
        $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    
    
        $mainImageData = $mediaUrl.'catalog/product'.$product->getData('thumbnail');

        $imagesItems = [];
        foreach ($this->getGalleryImages() as $image) {
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'img' => $image->getData('medium_image_url'),
                'full' => $image->getData('large_image_url'),
                'caption' => ($image->getLabel() ?: $this->getProduct()->getName()),
                'position' => $image->getPosition(),
                'isMain' => $this->isMainImage($image),
                'type' => str_replace('external-', '', $image->getMediaType()),
                'videoUrl' => $image->getVideoUrl(),
            ];
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $mainImageData,
                'img' => $mediaUrl.'catalog/product'.$product->getData('image'),
                'full' => $mediaUrl.'catalog/product'.$product->getData('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
                'type' => 'image',
                'videoUrl' => null,
            ];
        }
        return json_encode($imagesItems);
    }

    
}
