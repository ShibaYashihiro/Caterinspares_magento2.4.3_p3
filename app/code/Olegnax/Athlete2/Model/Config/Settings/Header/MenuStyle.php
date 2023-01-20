<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class MenuStyle implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'default',  'label' => __('Default')],
            ['value' => 'underline',  'label' => __('Underline')],
			['value' => 'long-top',  'label' => __('Long from top')],
        ];
    }
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
