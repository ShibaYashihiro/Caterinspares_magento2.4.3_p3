<?php
/**
 * Set layout for blog pages
 * 
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */
namespace Olegnax\Athlete2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config;
use \Magento\Store\Model\ScopeInterface;

class ChangeBlogLayout implements ObserverInterface {

	protected $config;
	protected $scopeConfig;

	public function __construct(
	Config $config, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->config		 = $config;
		$this->scopeConfig	 = $scopeConfig;
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
	}

	public function execute( Observer $observer ) {
		$variable = $this->getConfig( 'athlete2_settings/blog/blog_list_page_layout' );
		if ( !empty( $variable ) ) {
			$this->config->setPageLayout( $variable );
		}
	}

}
