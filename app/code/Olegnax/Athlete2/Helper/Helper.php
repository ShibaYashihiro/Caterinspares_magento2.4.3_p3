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

use Magento\Catalog\Helper\Product\Compare;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Helper\Data;
use Olegnax\Athlete2\Block\ChildTemplate;

class Helper extends AbstractHelper
{

	const CONFIG_MODULE = 'athlete2_settings';
	const PRODUCT_CODE = '23693737';
	const CHILD_TEMPLATE = ChildTemplate::class;

	/**
	 *
	 * @var ObjectManager
	 */
	public $objectManager;
	protected $_lazyExcludeClass;


	public function __construct(Context $context)
	{
		$this->objectManager = ObjectManager::getInstance();

		parent::__construct($context);
	}

	public function getLayoutTemplateHtml($block, $option_path = '', $fileName = '', $arguments = [])
	{
		$value = $this->getConfig($option_path);

		if (is_string($value) || is_numeric($value)) {
			return $this->getLayoutTemplateHtmlbyValue($block, $value, $fileName, $arguments);
		}
	}

	public function getConfig($path, $storeCode = null)
	{
		return $this->getSystemValue($path, $storeCode);
	}

	public function getSystemValue($path, $storeCode = null)
	{
		if ($this->isAdmin() && is_null($storeCode)) {
			return $this->_loadObject(\Magento\Backend\App\ConfigInterface::class)->getValue($path);
		}
		return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function isAdmin()
	{
		return $this->isArea(Area::AREA_ADMINHTML);
	}

	public function isArea($area = Area::AREA_FRONTEND)
	{
		if (!isset($this->isArea[$area])) {
			/** @var State $state */
			$state = $this->_loadObject(State::class);

			try {
				$this->isArea[$area] = ($state->getAreaCode() == $area);
			} catch (Exception $e) {
				$this->isArea[$area] = false;
			}
		}

		return $this->isArea[$area];
	}

	public function setModuleConfig($path = '', $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeCode = 0)
	{
		if (!empty($path)) {
			$path = static::CONFIG_MODULE . '/' . $path;
		}
		if (!empty($storeCode)) {
			$scope = ScopeInterface::SCOPE_STORE;
		}
		return $this->setSystemValue($path, $value, $scope, $storeCode);
	}

	public function setSystemValue($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $storeCode = 0)
	{
		if (!empty($storeCode)) {
			$scope = ScopeInterface::SCOPE_STORE;
		}
		return $this->_loadObject(WriterInterface::class)->save($path, $value, $scope, $storeCode);
	}

	public function getModuleConfig($path = '', $storeCode = null)
	{
		if (!empty($path)) {
			$path = static::CONFIG_MODULE . '/' . $path;
		}
		return $this->getSystemValue($path, $storeCode);
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	public function _loadObject($path)
	{
		return $this->objectManager->get($path);
	}

	/**
	 * @param string $path
	 * @param array $arguments
	 * @return mixed
	 */
	public function _createObject($path, $arguments = [])
	{
		return $this->objectManager->create($path, $arguments);
	}

	public function getLayoutTemplateHtmlbyValue($block, $value = null, $fileName = null, $arguments = [],
												 $separator = '/')
	{
		$_fileName = '';
		if (empty($fileName)) {
			$blockTemplate = $block->getTemplate();
			if (preg_match('/(\.[^\.]+?)$/', $blockTemplate)) {
				$fileName = preg_replace('/(\.[^\.]+?)$/', '%s%s$1', $blockTemplate);
			} else {
				$fileName .= '%s%s';
			}
		} else {
			$_fileName = $fileName;
		}
		$blockName = $separator . $block->getNameInLayout() . $separator . $_fileName . $separator . $value;
		$fileName = sprintf($fileName, $separator, $value);
		while ($block->getLayout()->getBlock($blockName)) {
			$blockName .= '_0';
		}
		$_block = $block->getLayout()->createBlock(static::CHILD_TEMPLATE, $blockName);
		$block->setChild($_block->getNameInLayout(), $_block);
		if (!empty($arguments) && is_array($arguments)) {
			foreach ($arguments as $key => $value) {
				$_block->addData($key, $value);
			}
		}
		$content = $_block->setTemplate($fileName)->toHtml();

		return $content;
	}

	public function getLayoutBlockHtml($block, $option_path = '', $blockName = null)
	{
		$value = $this->getConfig($option_path);

		if (is_string($value) || is_numeric($value)) {
			return $this->getBlockValueHtmlby($block, $value, $blockName);
		}
	}

	public function getLayoutBlockHtmlbyValue($block, $value = null, $blockName = null, $separator = '/')
	{
		if (empty($blockName)) {
			$blockName = $block->getNameInLayout();
		}
		$blockName = $blockName . $separator . $value;
		$_block = $block->getLayout()->getBlock($blockName);
		$content = '';
		if ($_block) {
			$block->setChild($blockName, $_block);
			$content = $_block->toHtml();

			return $content;
		}

		return $content;
	}

	public function isLoggedIn()
	{
		return $this->getSession()->isLoggedIn();
	}

	public function getSession()
	{
		return ObjectManager::getInstance()->create(Session::class);
	}

	function getWishlistCount()
	{
		$this->getSession();
		return $this->_loadObject(Data::class)->getItemCount();
	}

	function getCompareListUrl()
	{
		$this->getSession();
		return $this->_loadObject(Compare::class)->getListUrl();
	}

	function getCompareListCount()
	{
		$this->getSession();
		return $this->_loadObject(Compare::class)->getItemCount();
	}

	public function isActivePlugin($name)
	{
		return $this->_moduleManager->isOutputEnabled($name);
	}

	public function checkPurchaseCode($save = false)
	{
		$code = $this->getConfig('athlete2_license/general/code');
		$codeConfirm = $this->getConfig('athlete2_license/general/codeconfirm');
		$check = '';
		if ($save || empty($codeConfirm)) {
			$site_url = $this->scopeConfig->getValue('web/unsecure/base_url');
			$domain = parse_url($site_url, PHP_URL_HOST);
			if ((!$code && $codeConfirm) || ($codeConfirm && base64_encode($code) != $codeConfirm)) {
				$result = $this->curlPurchaseCode(base64_decode($codeConfirm), $domain, "remove");
				if (is_array($result) && !empty($result)) {
					if ($result['status'] == 1) {
						$check = 'remove';
						$codeConfirm = null;
					}
					if (isset($result['message'])) {
						$this->messageManager()->getMessages(true);
						$this->messageManager()->addWarning(__($result['message']));
					}
				} else {
					$this->messageManager()->getMessages(true);
					$this->messageManager()->addWarning(__("Can't connect to license server"));
				}
			} elseif ($code) {
				$result = $this->curlPurchaseCode($code, $domain, "add");
				if (is_array($result) && !empty($result)) {
					if ($result['status'] == 1) {
						$codeConfirm = base64_encode($code);
						$check = 'valid';
						$this->configFactory()->saveConfig('athlete2_license/general/item_name', $result['item_name'], "default", 0);
						$this->configFactory()->saveConfig('athlete2_license/general/created_at', $result['created_at'], "default", 0);
						$this->configFactory()->saveConfig('athlete2_license/general/licence', $result['licence'], "default", 0);
						$this->configFactory()->saveConfig('athlete2_license/general/supported_until', $result['supported_until'], "default", 0);
					} else {
						$check = '';
						$codeConfirm = null;
						$this->configFactory()->saveConfig('athlete2_license/general/code', null, "default", 0);
					}
					$this->clearCache();
				} else {
					$this->messageManager()->getMessages(true);
					$this->messageManager()->addWarning(__("Can't connect to license server"));
				}
				if (isset($result['message'])) {
					$this->messageManager()->getMessages(true);
					$this->messageManager()->addWarning(__($result['message']));
				}
			}

			$this->configFactory()->saveConfig('athlete2_license/general/codeconfirm', $codeConfirm, "default", 0);
		} else {
			if ($code && $codeConfirm && base64_encode($code) == $codeConfirm) {
				$check = "valid";
			}
		}

		return $check;
	}

	private function curlPurchaseCode($code, $domain, $action)
	{
		try {
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => "https://olegnax.com/extras/verify-purchase-envato/verify_purchase_new.php?" . http_build_query([
						'item' => self::PRODUCT_CODE,
						'version' => $this->getVersion(),
						'code' => $code,
						'domain' => $domain,
						'action' => $action,
					]),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_USERAGENT => 'ATHLETE-PURCHASE-VERIFY',
			]);
			$result = json_decode(curl_exec($ch), true);
			curl_close($ch);
		} catch (Exception $exc) {

			return null;
		}
		return $result;
	}

	private function getVersion()
	{
		return $this->_loadObject(ProductMetadataInterface::class)->getVersion();
	}

	private function messageManager()
	{
		return $this->_loadObject(ManagerInterface::class);
	}

	private function configFactory()
	{
		return $this->_loadObject(ConfigInterface::class);
	}

	public function clearCache($types = ['config'])
	{
		$cacheTypeList = $this->_loadObject(TypeListInterface::class);
		$CacheFrontendPool = $this->_loadObject(Pool::class);
		foreach ($types as $type) {
			$cacheTypeList->cleanType($type);
		}
		foreach ($CacheFrontendPool as $cacheFrontend) {
			$cacheFrontend->getBackend()->clean();
		}
	}

	public function getBlockTemplateProcessor($content = '')
	{
		return $this->_loadObject(FilterProvider::class)->getBlockFilter()->filter(trim($content));
	}

    public function getUrl($route = '', $params = [])
    {
        /** @var UrlInterface $urlBuilder */
        $urlBuilder = $this->_loadObject(UrlInterface::class);
        return $urlBuilder->getUrl($route, $params);
    }

    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
    public function isAccountPage()
    {
		$request = $this->objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        return $request->getFullActionName() == 'customer_account_login';
    }
}
