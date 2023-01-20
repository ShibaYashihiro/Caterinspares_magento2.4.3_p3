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

namespace Olegnax\Athlete2\Block\Html;

use \Olegnax\Athlete2\Helper\ProductImage as HelperProductImage;

class PrevNextProduct extends \Magento\Framework\View\Element\Template {

	/**
	 *
	 * @var \Olegnax\Athlete2\Helper\PrevNextProduct 
	 */
	protected $prevNextProductHelper;

	/**
	 *
	 * @var \Olegnax\Athlete2\Helper\ProductImage
	 */
	protected $imageHelper;
	protected $product;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Olegnax\Athlete2\Helper\PrevNextProduct $prevNextProductHelper,
		HelperProductImage $imageHelper,
		array $data = []
	) {
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->prevNextProductHelper = $prevNextProductHelper;
		$this->imageHelper = $imageHelper;
		parent::__construct($context, $data);
	}

	protected function _prepareLayout() {
		if ($this->getIsNext()) {
			$this->setData('product', $this->prevNextProductHelper->getNextProduct() );
		} else {
			$this->setData('product', $this->prevNextProductHelper->getPreviousProduct() );
		}

		return parent::_prepareLayout();
	}

	public function getName() {
		if ($this->isProductExists()) {
			return $this->getProduct()->getName();
		}
		return false;
	}

	public function getProductUrl() {
		if ($this->isProductExists()) {
			return $this->getProduct()->getProductUrl();
		}
		return false;
	}

	public function isProductExists() {
		return (bool) $this->getProduct();
	}

	public function getImage($imageId, $template = HelperProductImage::TEMPLATE, array $attributes = [], $properties = []) {
		if ($this->isProductExists()) {
			return $this->imageHelper->getImage($this->getProduct(), $imageId, $template,
							$attributes, $properties);
		}
		return false;
	}

	public function getImageHover($imageId, $imageId_hover, $template = HelperProductImage::HOVER_TEMPLATE, array $attributes = [], $properties = []) {
		if ($this->isProductExists()) {
			return $this->imageHelper->getImageHover($this->getProduct(), $imageId,
							$imageId_hover, $template, $attributes, $properties);
		}
		return false;
	}

	public function getResizedImage($imageId, $size, $template = HelperProductImage::TEMPLATE, array $attributes = [], $properties = []) {
		if ($this->isProductExists()) {
			return $this->imageHelper->getResizedImage($this->getProduct(), $imageId, $size,
							$template, $attributes, $properties);
		}
		return false;
	}

	public function getResizedImageHover($imageId, $imageId_hover, $size, $template = HelperProductImage::HOVER_TEMPLATE, array $attributes = [], $properties = []) {
		if ($this->isProductExists()) {
			return $this->imageHelper->getResizedImageHover($this->getProduct(), $imageId,
							$imageId_hover, $size, $template, $attributes, $properties);
		}
		return false;
	}

	public function hasHoverImage($imageId, $imageId_hover) {
		if ($this->isProductExists()) {
			return $this->imageHelper->hasHoverImage($this->getProduct(), $imageId,
							$imageId_hover);
		}
		return false;
	}

}
