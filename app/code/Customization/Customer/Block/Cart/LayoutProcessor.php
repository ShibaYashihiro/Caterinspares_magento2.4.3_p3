<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Customization\Customer\Block\Cart;

class LayoutProcessor extends \Magento\Checkout\Block\Cart\LayoutProcessor
{
    
    protected function isCityActive()
    {
        return true;
    }
}
