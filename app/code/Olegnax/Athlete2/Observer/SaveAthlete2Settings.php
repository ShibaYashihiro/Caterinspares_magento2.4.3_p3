<?php

/**
 * Save Athlete2 Settings interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Model\DynamicStyle\Generator as DynamicStyleGenerator;

class SaveAthlete2Settings implements ObserverInterface
{

    /**
     * Dynamic Style generator
     *
     * @var DynamicStyleGenerator
     */
    protected $_DynamicStyleGenerator;

    /**
     *
     *
     * @var Helper
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param DynamicStyleGenerator $generator
     */
    public function __construct(DynamicStyleGenerator $generator, Helper $helper)
    {
        $this->_DynamicStyleGenerator = $generator;
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->_helper->checkPurchaseCode(true);
        $this->_DynamicStyleGenerator->generate('settings', $observer->getData('website'), $observer->getData('store'));
    }
}
