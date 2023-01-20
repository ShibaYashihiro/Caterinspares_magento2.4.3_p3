<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Catalog\Products;

class ProductsLayout implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray() {
		$optionArray = [ ];
		$array		 = $this->toArray();
		foreach ( $array as $key => $value ) {
			$optionArray[] = [ 'value' => $key, 'label' => $value ];
		}

		return $optionArray;
	}

    public function toArray()
    {
        return [
            '1' => __('All Actions Above Image, Centered'),
            '2' => __('Quicview Above Image, Bottom'),
        ];
    }
}
