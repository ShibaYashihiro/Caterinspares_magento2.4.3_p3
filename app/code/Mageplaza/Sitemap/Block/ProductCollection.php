<?php
namespace Mageplaza\Sitemap\Block;

class ProductCollection extends \Magento\Framework\View\Element\Template
{
     protected $_productFactory; 
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $ProductFactory,
        array $data = []
     ) {
        $this->_productFactory = $ProductFactory;
        parent::__construct($context, $data);
        //get collection of data 
        $collection = $this->_productFactory->create()->getCollection();
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('My Grid List'));
    }
 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection 
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'webkul.grid.record.pager'
            )->setAvailableLimit(array(100=>100,200=>200,300=>300))->setShowPerPage(true)->setCollection(
                $this->getCollection() // assign collection to pager
            );

            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }
 
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }   
}
?>