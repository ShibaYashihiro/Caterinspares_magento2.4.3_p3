<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Type implements ArrayInterface
{
    const TYPE_DISABLED = '';
    const TYPE_BUTTON = 'button';

    public function toOptionArray()
    {
        return [
            ['value' => static::TYPE_DISABLED, 'label' => __('Disable Module')],
            ['value' => 'auto', 'label' => __('On Scroll - automatically on page scroll')],
            ['value' => static::TYPE_BUTTON, 'label' => __('Button - Manually on button click')]
        ];
    }

    public function toArray()
    {
        return [
            static::TYPE_DISABLED => __('Disable Module'),
            'auto' => __('On Scroll - automatically on page scroll'),
            static::TYPE_BUTTON => __('Button - Manually on button click')
        ];
    }
}
