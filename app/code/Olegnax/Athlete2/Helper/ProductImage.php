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
namespace Olegnax\Athlete2\Helper;

use \Magento\Catalog\Block\Product\Image as CatalogBlockProductImage;
use \Magento\Catalog\Model\Product;

/**
 * Description of Image
 *
 * @author Master
 */
class ProductImage extends \Magento\Framework\App\Helper\AbstractHelper {

	/**
	 * @var \Magento\Catalog\Helper\Image
	 */
	protected $imageHelper;
	public $_objectManager;

	/**
	 * @var ParamsBuilder
	 */
	private $imageParamsBuilder;

	/**
	 * @var \Magento\Framework\View\ConfigInterface
	 */
	protected $viewConfig;

	const TEMPLATE		 = 'Magento_Catalog::product/image_with_borders.phtml';
	const HOVER_TEMPLATE	 = 'Magento_Catalog::product/hover_image_with_borders.phtml';

	/**
	 * @var \Magento\Framework\Config\View
	 */
	protected $configView;

	public function __construct(
	\Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Helper\Image $imageHelper,
 \Magento\Framework\View\ConfigInterface $viewConfig,
 \Magento\Catalog\Model\Product\Image\ParamsBuilder $imageParamsBuilder
	) {

		$this->_objectManager		 = \Magento\Framework\App\ObjectManager::getInstance();
		$this->imageHelper			 = $imageHelper;
		$this->viewConfig			 = $viewConfig;
		$this->imageParamsBuilder	 = $imageParamsBuilder;
		parent::__construct( $context );
	}

	public function getImage( Product $product, $imageId, $template = self::TEMPLATE, array $attributes = [],
						   $properties = [] ) {
		$image			 = $this->_getImage( $product, $imageId, $properties );
		$imageMiscParams = $this->getImageParams( $imageId );

		$data = [
			'data' => [
				'template'			 => $template,
				'product_id'		 => $product->getId(),
				'image_id'			 => $imageId,
				'image_url'			 => $image->getUrl(),
				'label'				 => $this->getLabel( $product, $imageMiscParams[ 'image_type' ] ),
				'width'				 => $imageMiscParams[ 'image_width' ],
				'height'			 => $imageMiscParams[ 'image_height' ],
				'ratio'				 => $this->getRatio( $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ),
				'custom_attributes'	 => $this->getStringCustomAttributes( $attributes ),
			],
		];

		return $this->_createTemplate( $data );
	}

	public function getImageHover( Product $product, $imageId, $imageId_hover, $template = self::HOVER_TEMPLATE,
								array $attributes = [], $properties = [] ) {
		if ( !$this->hasHoverImage( $product, $imageId, $imageId_hover ) ) {
			return $this->getImage( $product, $imageId, self::TEMPLATE, $attributes, $properties );
		}

		$image					 = $this->_getImage( $product, $imageId, $properties )->getUrl();
		$imageMiscParams		 = $this->getImageParams( $imageId );
		$image_hoverMiscParams	 = $this->getImageParams( $imageId_hover );

		$image_hover = $this->resizeImage( $product, $imageId_hover, [ $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ], $properties )->getUrl();

		$data = [
			'data' => [
				'template'			 => $template,
				'product_id'		 => $product->getId(),
				'image_id'			 => $imageId,
				'image_hover_id'	 => $imageId_hover,
				'image_url'			 => $image,
				'image_hover_url'	 => $image_hover,
				'label'				 => $this->getLabel( $product, $imageMiscParams[ 'image_type' ] ),
				'label_hover'		 => $this->getLabel( $product, $image_hoverMiscParams[ 'image_type' ] ),
				'width'				 => $imageMiscParams[ 'image_width' ],
				'height'			 => $imageMiscParams[ 'image_height' ],
				'ratio'				 => $this->getRatio( $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ),
				'custom_attributes'	 => $this->getStringCustomAttributes( $attributes ),
			],
		];

		return $this->_createTemplate( $data );
	}

	public function getResizedImage( Product $product, $imageId, $size, $template = self::TEMPLATE, array $attributes = [],
								  $properties = [] ) {
		$imageMiscParams = $this->getImageParams( $imageId );
		if ( empty( $size ) ) {
			return $this->getImage( $product, $imageId, $template, $attributes, $properties );
		}
		if ( is_array( $size ) ) {
			foreach ( [ 'image_width', 'image_height' ] as $key => $value ) {
				if ( !isset( $size[ $key ] ) || empty( $size[ $key ] ) ) {
					$size[ $key ] = $imageMiscParams[ $value ];
				}
			}
		}
		$image			 = $this->resizeImage( $product, $imageId, $size, $properties );
		$imageMiscParams = $this->getImageParams( $imageId );
		list($imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ]) = $image->getResizedImageInfo();

		$data = [
			'data' => [
				'template'			 => $template,
				'product_id'		 => $product->getId(),
				'image_id'			 => $imageId,
				'image_url'			 => $image->getUrl(),
				'label'				 => $this->getLabel( $product, $imageMiscParams[ 'image_type' ] ),
				'width'				 => $imageMiscParams[ 'image_width' ],
				'height'			 => $imageMiscParams[ 'image_height' ],
				'ratio'				 => $this->getRatio( $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ),
				'custom_attributes'	 => $this->getStringCustomAttributes( $attributes ),
			],
		];

		return $this->_createTemplate( $data );
	}

	public function getResizedImageHover( Product $product, $imageId, $imageId_hover, $size,
									   $template = self::HOVER_TEMPLATE, array $attributes = [], $properties = [] ) {
		if ( !$this->hasHoverImage( $product, $imageId, $imageId_hover ) ) {
			return $this->getResizedImage( $product, $imageId, $size, self::TEMPLATE, $attributes, $properties );
		}
		$imageMiscParams = $this->getImageParams( $imageId );
		if ( empty( $size ) ) {
			$size = [ $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ];
		} elseif ( is_array( $size ) ) {
			foreach ( [ 'image_width', 'image_height' ] as $key => $value ) {
				if ( !isset( $size[ $key ] ) || empty( $size[ $key ] ) ) {
					$size[ $key ] = $imageMiscParams[ $value ];
				}
			}
		}

		$image					 = $this->resizeImage( $product, $imageId, $size, $properties );
		list($imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ]) = $image->getResizedImageInfo();
		$image					 = $image->getUrl();
		$image_hover			 = $this->resizeImage( $product, $imageId_hover, $size, $properties )->getUrl();
		$image_hoverMiscParams	 = $this->getImageParams( $imageId_hover );

		$data = [
			'data' => [
				'template'			 => $template,
				'product_id'		 => $product->getId(),
				'image_id'			 => $imageId,
				'image_hover_id'	 => $imageId_hover,
				'image_url'			 => $image,
				'image_hover_url'	 => $image_hover,
				'label'				 => $this->getLabel( $product, $imageMiscParams[ 'image_type' ] ),
				'label_hover'		 => $this->getLabel( $product, $image_hoverMiscParams[ 'image_type' ] ),
				'width'				 => $imageMiscParams[ 'image_width' ],
				'height'			 => $imageMiscParams[ 'image_height' ],
				'ratio'				 => $this->getRatio( $imageMiscParams[ 'image_width' ], $imageMiscParams[ 'image_height' ] ),
				'custom_attributes'	 => $this->getStringCustomAttributes( $attributes ),
			],
		];

		return $this->_createTemplate( $data );
	}

	private function _createTemplate( $data = [] ) {
		return $this->_objectManager->create( CatalogBlockProductImage::class, $data );
	}

	private function _getImage( Product $product, $imageId, $properties = [] ) {
		return $this->imageHelper->init( $product, $imageId, $properties );
	}

	public function resizeImage( Product $product, $imageId, $size, $properties = [] ) {
		$size	 = $this->prepareSize( $size );
		$image	 = $this->_getImage( $product, $imageId, $properties );
		$image->resize( $size[ 0 ], $size[ 1 ] );

		return $image;
	}

	public function hasHoverImage( Product $product, $imageId, $imageId_hover ) {
		if ( $imageId != $imageId_hover ) {
			$_imageId		 = $this->getImageParams( $imageId );
			$_imageId_hover	 = $this->getImageParams( $imageId_hover );
			if ( $_imageId[ 'image_type' ] !== $_imageId_hover[ 'image_type' ] ) {
				$image		 = $product->getData( $_imageId[ 'image_type' ] );
				$image_hover = $product->getData( $_imageId_hover[ 'image_type' ] );
				return $image && $image_hover && 'no_selection' !== $image_hover && $image !== $image_hover;
			}
		}

		return false;
	}

	public function getUrlResizedImage( Product $product, $image, $size, $properties = [] ) {
		$image = $this->resizeImage( $product, $image, $size, $properties );
		return $image->getUrl();
	}

	private function prepareSize( $size ) {
		if ( is_array( $size ) && 1 >= count( $size ) ) {
			$size = array_shift( $size );
		}
		if ( !is_array( $size ) ) {
			$size = [ $size, $size ];
		}
		$size	 = array_map( 'floatval', $size );
		$size	 = array_map( 'abs', $size );
		return $size;
	}

	/**
	 * Calculate image ratio
	 *
	 * @param $width
	 * @param $height
	 * @return float
	 */
	private function getRatio( int $width, int $height ): float {
		if ( $width && $height ) {
			return $height / $width;
		}
		return 1.0;
	}

	/**
	 * @param Product $product
	 *
	 * @param string $imageType
	 * @return string
	 */
	private function getLabel( Product $product, string $imageType ): string {
		$label = $product->getData( $imageType . '_' . 'label' );
		if ( empty( $label ) ) {
			$label = $product->getName();
		}
		return (string) $label;
	}

	/**
	 * Retrieve config view
	 *
	 * @return \Magento\Framework\Config\View
	 */
	protected function getConfigView() {
		if ( !$this->configView ) {
			$this->configView = $this->viewConfig->getViewConfig();
		}
		return $this->configView;
	}

	/**
	 * 
	 * 
	 * @return array
	 */
	protected function getImageParams( $imageId ) {
		$viewImageConfig = $this->getConfigView()->getMediaAttributes( 'Magento_Catalog', \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE, $imageId );

		$imageMiscParams = $this->imageParamsBuilder->build( $viewImageConfig );

		return $imageMiscParams;
	}

	/**
	 * Retrieve image custom attributes for HTML element
	 *
	 * @param array $attributes
	 * @return string
	 */
	private function getStringCustomAttributes( array $attributes ): string {
		$result = [];
		foreach ( $attributes as $name => $value ) {
			$result[] = $name . '="' . $value . '"';
		}
		return !empty( $result ) ? implode( ' ', $result ) : '';
	}

}
