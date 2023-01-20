<?php

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class MoveReviews implements ObserverInterface
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');
        if (!in_array($fullActionName, ['catalog_product_view', 'ox_quickview_catalog_product_view'])) {
            return $this;
        }

        $reviewsInTab = $this->getConfig('product/reviews_in_tab');
        $tabsInInfo = $this->getConfig('product/product_tabs_right');
        if (!$reviewsInTab) {
            $layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_view_move_review');
        }
        if ($tabsInInfo) {
            $layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_right');
        }

        return $this;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue('athlete2_settings/' . $path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }
}
