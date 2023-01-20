<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Olegnax\Athlete2\Helper\Helper as HelperHelper;

class TopLinks extends SimpleTemplate
{

	public function getCompareListUrl()
	{
        return $this->_loadObject(HelperHelper::class)->getCompareListUrl();
    }

}
