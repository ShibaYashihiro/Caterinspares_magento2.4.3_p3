<?php

namespace Olegnax\Quickview\Controller\Index;
use Magento\Store\Model\ScopeInterface;

class CheckInstall extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
    	//echo $_SERVER['REMOTE_ADDR'];die('24234234');

  ///  	\Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
//        \Magento\Store\Model\StoreManagerInterface $storeManager


        $simiObjectManager = $this->_objectManager;
        $configWriter = $simiObjectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
        //$configWriter->save('catalog/layered_navigation/display_category', 1, ScopeInterface::SCOPE_STORES, 1);


        $config = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('catalog/layered_navigation/display_category', ScopeInterface::SCOPE_STORES);

        //$category = $simiObjectManager->create('Magento\Catalog\Model\Category')->load(219);
        //$category->setIsAnchor(1)->save();
        var_dump(ScopeInterface::SCOPE_STORES);
        echo "<br/>";
        var_dump($config);
        die('219');
    }
}
