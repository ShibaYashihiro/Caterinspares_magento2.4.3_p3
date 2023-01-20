<?php
/**
 * Resize Blog Featured Image
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Plugin\Blog\Model;

class Post {

	protected $imageHelper;
	protected $scopeConfig;

	public function __construct(
	\Olegnax\Athlete2\Helper\Image $imageHelper, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->imageHelper	 = $imageHelper;
		$this->scopeConfig	 = $scopeConfig;
	}

	public function aroundGetFeaturedImage( $subject, $proceed, $size = null,
										 $attributes = [ 'aspect_ratio' => true, 'crop' => true, ] ) {
		if ( !empty( $size ) ) {
			list($width, $height) = $this->prepareSize( $size );
			$dataName = sprintf( 'featured_image_resized_%s_%s', $width, $height );
			if ( !$subject->hasData( $dataName ) ) {
				if ( $file = $subject->getData( 'featured_img' ) ) {
					$image = $this->imageHelper->adaptiveResize( $file, $size, $attributes )->getUrl();
				} else {
					$image = false;
				}
				$subject->setData( $dataName, $image );
				$subject->setData( 'featured_image', $image );
			}
		}

		return $proceed();
	}

	private function prepareSize( $size ) {
		if ( is_array( $size ) && 1 >= count( $size ) ) {
			$size = array_shift( $size );
		}
		if ( !is_array( $size ) ) {
			$size = [ $size, $size ];
		}
		$size	 = array_map( 'intval', $size );
		$size	 = array_map( 'abs', $size );
		return $size;
	}

}
