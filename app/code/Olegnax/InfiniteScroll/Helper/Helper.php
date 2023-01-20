<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Helper;

use Olegnax\Core\Helper\Helper as CoreHelperHelper;
use Olegnax\InfiniteScroll\Model\Config\Source\Type;

class Helper extends CoreHelperHelper
{

    const CONFIG_MODULE = 'olegnax_infinitescroll';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Type::TYPE_DISABLED != $this->getModuleConfig('general/type');
    }
}
