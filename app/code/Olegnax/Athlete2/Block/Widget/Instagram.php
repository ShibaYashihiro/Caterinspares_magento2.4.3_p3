<?php

namespace Olegnax\Athlete2\Block\Widget;

use Olegnax\Athlete2\Model\InstagramAPI;
use Olegnax\Athlete2\Model\InstagramException;
use Magento\Widget\Block\BlockInterface;
use Olegnax\Athlete2\Block\Template;

class Instagram extends Template implements BlockInterface {

	const IMAGE_RESOLUTION = 'standard_resolution';

	protected function _construct() {
		$this->addData( [
			'cache_lifetime' => 86400,
		] );
		if ( !$this->hasData( 'template' ) && !$this->getTemplate() ) {
			$this->setTemplate( 'Olegnax_Athlete2::instagram_list.phtml' );
		}
		parent::_construct();
	}

	public function getImages() {
		$access_token	 = $this->getConfig( 'athlete2_settings/social/instagram_access_token' );
		$limit			 = intval( $this->getData( 'images_count' ) );
		$images			 = [];
		if ( $access_token ) {
			$_images = [];
			try {
				$api		 = new InstagramAPI( $access_token );
				$items		 = $api->getUserMedia( 'self', $limit * 2 );
				$resolution	 = self::IMAGE_RESOLUTION;
				foreach ( $items->data as $item ) {
					if ( isset( $item->user ) ) {
						unset( $item->user );
					}
					if ( isset( $item->users_in_photo ) ) {
						unset( $item->users_in_photo );
					}
					if ( isset( $item->caption ) ) {
						$item->caption = $item->caption->text;
					} else {
						$item->caption = '';
					}
					if ( isset( $item->likes ) ) {
						$item->likes = $item->likes->count;
					}
					if ( isset( $item->comments ) ) {
						$item->comments = $item->comments->count;
					}
					if ( isset( $item->images ) ) {
						$item->image = $item->images->$resolution;
						unset( $item->images );
					}
					if ( isset( $item->videos ) ) {
						$item->video = $item->videos->$resolution;
						unset( $item->videos );
					}
					if ( isset( $item->carousel_media ) ) {
						foreach ( $item->carousel_media as $key => $value ) {
							if ( isset( $value->users_in_photo ) ) {
								unset( $value->users_in_photo );
							}

							if ( isset( $value->images ) ) {
								$value->image = $value->images->$resolution;
								unset( $value->images );
							}
							if ( isset( $value->videos ) ) {
								$value->video = $value->videos->$resolution;
								unset( $value->videos );
							}
							$item->carousel_media[ $key ] = $value;
						}
					}
					$images[] = $item;
					if ( $limit <= count( $images ) ) {
						break;
					}
				}
			} catch ( InstagramException $exc ) {
				echo $exc->getMessage();
			}
		}
		return $images;
	}

	public function getUnderlineNameInLayout() {
		$name	 = $this->getNameInLayout();
		$name	 = preg_replace( '/[^a-zA-Z0-9_]/i', '_', $name );
		$name	 .= substr( md5( microtime() ), -5 );

		return $name;
	}

    /**
     * @return bool
     */
    public function isLazyLoadEnabled()
    {
		return $this->getConfig('athlete2_settings/general/lazyload');
    }

}
