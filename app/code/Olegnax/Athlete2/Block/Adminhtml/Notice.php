<?php

namespace Olegnax\Athlete2\Block\Adminhtml;

class Notice extends \Olegnax\Athlete2\Block\Template {

	protected function _toHtml() {
		$supportedUntil	 = $this->getConfig( 'athlete2_license/general/supported_until' );		
		$code			 = $this->getConfig( 'athlete2_license/general/code' );
		$codeConfirm	 = $this->getConfig( 'athlete2_license/general/codeconfirm' );
		if ( empty($codeConfirm) ) {
			$this->_loadObject( '\Olegnax\Athlete2\Helper\Helper' )->checkPurchaseCode();
		}
		$codeConfirm	 = $this->getConfig( 'athlete2_license/general/codeconfirm' );
		$status			 = $code && $codeConfirm && base64_encode( $code ) == $codeConfirm;
		$section		 = $this->getRequest()->getParam( 'section' );
		if ( 'athlete2_license' !== $section && !$status ) {
			$notice = '<div class="ox-license-status ox-notice-license status-disable"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __( 'Athlete2 Theme ' ) . '<span class="undelined">' . __( 'Not Activated!' ) . '</span><a href="' . $this->getLicenseUrl() . '">' . __( 'Click here to Activate' ) . '</a></h2></div></div>';
			return $notice;
		}

		return '';
	}

	protected function getLicenseUrl() {
		return $this->getUrl( '*/*/*', [ '_current' => true, 'section' => 'athlete2_license' ] );
	}

}
