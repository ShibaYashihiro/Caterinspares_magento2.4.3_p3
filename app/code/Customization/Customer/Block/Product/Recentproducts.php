<?php



namespace Customization\Customer\Block\Product;


use Magento\Reports\Block\Product\Viewed as ReportProductViewed;

class Recentproducts extends \Magento\Framework\View\Element\Template
{
	protected $reportProductViewed;
	
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context, 
    	ReportProductViewed $reportProductViewed
    )
    {
    	$this->reportProductViewed = $reportProductViewed;
        parent::__construct($context);
    }

    public function sayHello()
    {
        return __('Hello World');
    }
    public function getProductCollection()
    {
        return $this->reportProductViewed->getItemsCollection()->setPageSize($this->getProductsCount());
    }
}