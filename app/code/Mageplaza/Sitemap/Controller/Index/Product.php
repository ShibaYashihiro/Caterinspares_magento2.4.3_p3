<?php
namespace Mageplaza\Sitemap\Controller\Index;

class Product extends \Magento\Framework\App\Action\Action
{
  public function execute()
  {
	 $this->_view->loadLayout();
	 $this->_view->renderLayout();
  }
}
?>