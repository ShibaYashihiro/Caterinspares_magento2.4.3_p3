<?php

/**
 * Olegnax BrandSlider
 * 
 * This file is part of Olegnax/Core.
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
 * @package     Olegnax_BrandSlider
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\BrandSlider\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;

class BrandSlider extends Template implements BlockInterface {

	const BRANDS_PATH = 'wysiwyg/brands/';

	protected $repository;
	protected $_mediaDirectory;

	/**
	 * @var  array|null
	 */
	protected $items;

	/**
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext;

	/**
	 * Json Serializer Instance
	 *
	 * @var Json
	 */
	private $json;

	public function __construct(
	\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\App\Http\Context $httpContext,
 \Magento\Catalog\Model\Product\Attribute\Repository $repository, \Magento\Framework\Filesystem $filesystem,
 array $data = [], \Magento\Framework\Serialize\Serializer\Json $json = null
	) {
		$this->repository		 = $repository;
		$this->_mediaDirectory	 = $filesystem->getDirectoryRead( DirectoryList::MEDIA );
		$this->httpContext		 = $httpContext;
		$this->json				 = $json ?: \Magento\Framework\App\ObjectManager::getInstance()->get( \Magento\Framework\Serialize\Serializer\Json::class );
		parent::__construct( $context, $data );
	}

	protected function _construct() {
		$this->addData( [
			'cache_lifetime' => 86400,
		] );
		if ( !$this->hasData( 'template' ) && !$this->getTemplate() ) {
			$this->setTemplate( 'Olegnax_BrandSlider::brandslider.phtml' );
		}
		parent::_construct();
	}

	public function getCacheKeyInfo( $newval = [] ) {
		return array_merge( [
			'OLEGNAX_BRANDSLIDER_WIDGET',
			$this->_storeManager->getStore()->getId(),
			$this->_design->getDesignTheme()->getId(),
			$this->httpContext->getValue( \Magento\Customer\Model\Context::CONTEXT_GROUP ),
			$this->json->serialize( $this->getRequest()->getParams() ),
			$this->json->serialize( $this->getData() ),
		], parent::getCacheKeyInfo(), $newval );
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->_scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
	}

	public function getSliderId() {
		return 'ox_' . $this->getNameInLayout();
	}

	public function prepareStyle( array $style, string $separatorValue = ': ', string $separatorAttribute = ';' ) {
		$style = array_filter( $style );
		if ( empty( $style ) ) {
			return '';
		}
		foreach ( $style as $key => &$value ) {
			$value = $key . $separatorValue . $value;
		}
		$style = implode( $separatorAttribute, $style );

		return $style;
	}

	public function getResponsive( $to_string = true ) {
		$width		 = $this->getWidth();
		$responsive	 = [
			0 => [
				'items' => 1,
			],
		];
		$j			 = 1;
		for ( $i = $width + 1; $i < 3000; $i += $width ) {
			$j++;
			$responsive[ $i ] = [
				'items' => $j,
			];
		}

		if ( $to_string ) {
			return json_encode( $responsive );
		}

		return $responsive;
	}

	public function getAutoScroll() {
		$auto_scroll = $this->getData( 'auto_scroll' );
		if ( empty( $auto_scroll ) ) {
			$auto_scroll = 0;
		}

		return $auto_scroll;
	}

	public function getItems() {
		if ( $this->items === null ) {
			$this->items	 = [];
			$attributeCode	 = $this->getConfig( 'olegnax_brandslider/general/attribute_code' );
			if ( $attributeCode == '' ) {
				return $this->items;
			}

			$options = $this->repository->get( $attributeCode )->getOptions();

			$path = $this->_mediaDirectory->getAbsolutePath( self::BRANDS_PATH );
			array_shift( $options );
			foreach ( $options as &$option ) {
				$name	 = str_replace( [ ' ', '\'', '/', ':', '*', '?', '"', '<', '>', '|', '+', '.' ], '_', strtolower( $option->getLabel() ) );
				$paths	 = glob( $path . $name . '.*' );
				if ( !empty( $paths ) ) {
					$file_name = basename( array_shift( $paths ) );
					$option->setData( 'image_name', self::BRANDS_PATH . $file_name );
				} else {
					$option->setData( 'image_name', self::BRANDS_PATH . $name . '.png' );
				}
			}
			$this->items = $options;
		}

		return $this->items;
	}

	public function getBrandUrl( $item ) {
		return $this->getUrl( 'catalogsearch/result/' ) . '?q=' . $item->getLabel();
	}

}
