<?php
namespace Customization\Category\Block;

use Magento\Catalog\Model\CategoryFactory;

class Category extends \Magento\Framework\View\Element\Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    protected $_categoryCollectionFactory;
    protected $categoryFlatConfig;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFlatConfig = $categoryFlatState;
        parent::__construct($context, $data);
    }

    public function getCategoryCollection() {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addIsActiveFilter(); 
        return $collection;
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
        $subCategory = $subcateobj->load(316);
        $subCats = $subCategory->getChildrenCategories();
        return $subCats;
    }
    public function getChildCategories($category)
    {
       if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }
}