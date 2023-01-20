<?php
namespace Customization\Manufacturer\Block;
use Magento\Catalog\Model\CategoryFactory;

class Manufacturer extends \Magento\Framework\View\Element\Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    public function getmanufacturer(){
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        $attribute = $om->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class)
            ->get('manufacturer');
        return $attribute->getOptions();
        // foreach ($attribute->getOptions() as $option) {
        //      var_dump($option->getValue() . ' -> ' . $option->getLabel());
        // }
    }
    public function getcategories()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $subcateobj = $objectManager->create('Magento\Catalog\Model\Category');
        $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryCollection->create();
        // $categories->addAttributeToSelect('*');
        $categories->addLevelFilter('level', 2);
        $categoryHelper = $objectManager->get('\Magento\Catalog\Helper\Category');
        $categories = $categoryHelper->getStoreCategories();
        
        foreach ($categories as $category) {
            if(strcmp(strtoupper($category->getName()),"SHOP BY CATEGORY")){
                $catid = $category->getId();
            }
        }
        $subCategory = $subcateobj->load(312);
        $subCats = $subCategory->getChildrenCategories();
        return $subCats;
    }
}