<?php

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config;
use \Magento\Store\Model\ScopeInterface;

class AddBodyClass implements ObserverInterface
{
	protected $config;
	protected $scopeConfig;

	public function __construct(
		Config $config,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	){
		$this->config = $config;
		$this->scopeConfig = $scopeConfig;
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
	}

	public function execute(Observer $observer){
		$class= [];
		$class[] = 'menu--align-' . $this->getConfig('athlete2_settings/header/menu_align');
		/* $class[] = 'menu--style-' . $this->getConfig('athlete2_settings/header/menu_style'); */
		$class[] = 'menu--style-long-top';
		$class[] = 'minicart--style-' . $this->getConfig('athlete2_settings/header/minicart_style');
		$class[] = 'mobile-header--layout-' . $this->getConfig('athlete2_settings/mobile_header/mobile_header_layout');
		$class[] = 'footer--layout-' . $this->getConfig('athlete2_settings/footer/footer_layout');
		$header_layout = $this->getConfig('athlete2_settings/header/header_layout');
		$class[] = 'header--layout-' . $header_layout[-1];
		
		if ( 1 == $this->getConfig('athlete2_settings/contacts_page/contacts_page_fullwidth')) {
			$class[] = 'contacts-fullwidth';
		}
		if ( 2 == $this->getConfig('athlete2_settings/header/menu_position')) {
			$class[] = 'menu-position--below';
		}
		if ( 1 == $this->getConfig('athlete2_settings/header/minicart_btn_minimal')) {
			$class[] = 'minicart-btn--minimal';
		}
		if ( 1 == $this->getConfig('athlete2_settings/header/minicart_btn_hide_icon') && !$this->getConfig('athlete2_settings/header/minicart_btn_minimal')) {
			$class[] = 'minicart-btn__icon--hide';
		}
		if( 'main' != $this->getConfig('athlete2_settings/mobile_header/wishlist_mobile_position') && '' != $this->getConfig('athlete2_settings/mobile_header/wishlist_mobile_position')){
			$class[] = 'mobile-header__wishlist--hide';
		}
		if( 'main' != $this->getConfig('athlete2_settings/mobile_header/compare_mobile_position') && '' != $this->getConfig('athlete2_settings/mobile_header/compare_mobile_position') ){
			$class[] = 'mobile-header__compare--hide';
		}
		if ($this->getConfig('athlete2_settings/header/sticky_header') && $this->getConfig('athlete2_settings/header/sticky_header_smart')){
			$class[] = 'sticky-smart';
		} else {
			$class[] = 'sticky-simple';
		}
		if ($this->getConfig('athlete2_settings/header/sticky_header') && $this->getConfig('athlete2_settings/header/sticky_header_minimized')){
			$class[] = 'sticky-minimized';
		}
		if ($this->getConfig('athlete2_design/appearance_general/inputs_underlined')){
			$class[] = 'inputs-style--underlined';
		}
		if ( $this->getConfig('athlete2_settings/products_listing/quickview_mobile')) {
			$class[] = 'quickview-mobile--hide';
		}
		
		foreach ($class as $_class) {
			$this->addBodyClass($_class);
		}

	}

	public function addBodyClass($className)
	{
		$className = strtolower($className);
		$bodyClasses = $this->config->getElementAttribute(\Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY, \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS);
		$bodyClasses = $bodyClasses ? explode(' ', $bodyClasses) : [];
		$bodyClasses[] = $className;
		$bodyClasses = array_unique($bodyClasses);
		$this->config->setElementAttribute(
			\Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY,
			\Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS,
			implode(' ', $bodyClasses)
		);
		return $this;
	}
}
