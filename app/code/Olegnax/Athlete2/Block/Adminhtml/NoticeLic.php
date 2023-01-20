<?php

namespace Olegnax\Athlete2\Block\Adminhtml;

class NoticeLic extends \Olegnax\Athlete2\Block\Template {

	protected function _toHtml() {
		$supportedUntil	 = $this->getConfig( 'athlete2_license/general/supported_until' );
		$supportStatus			= false;
		$code			 = $this->getConfig( 'athlete2_license/general/code' );
		$codeConfirm	 = $this->getConfig( 'athlete2_license/general/codeconfirm' );
		if ( empty($codeConfirm) ) {
			$this->_loadObject( '\Olegnax\Athlete2\Helper\Helper' )->checkPurchaseCode();
		}
		$notice = '';
		$codeConfirm	 = $this->getConfig( 'athlete2_license/general/codeconfirm' );
		$status			 = $code && $codeConfirm && base64_encode( $code ) == $codeConfirm;
		$section		 = $this->getRequest()->getParam( 'section' );
		if ( 'athlete2_license' == $section ) {
			if ( $status ) {
				$notice.='<div class="two-blocks">';
			}
			$notice .= '<div class="ox-license-status status-' . ($status ? 'active' : 'disable') . '"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __( 'Theme License ' ) . '<span class="undelined">' . ($status ? __( 'Activated' ) : __( 'Not Activated!' )) . '</span></h2></div></div>';
			if ( $status ) {
				if(empty( $supportedUntil )){
					$notice .= '<div class="ox-license-status support-expired"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __( 'Theme Support has ' ) . '<span class="undelined">' . __( 'Expired' ) . '</span></h2></div><div class="right"><a href="https://themeforest.net/item/athlete2-strong-magento-2-theme/23693737" target="_blank" class="button">' . __( 'renew' ) . '</a></div></div>';
				}
				$notice .= '<div class="ox-license-status support-active"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __( 'Theme Support is ' ) . '<span class="undelined">' . __( 'Active' ) . '</span></h2></div></div>';
			
			}
			if ( $status ) {
				$notice.='</div>';
			}
			return $notice;
		}

		return '';
	}

}
