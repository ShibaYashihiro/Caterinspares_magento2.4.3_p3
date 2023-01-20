<?php

namespace Olegnax\Athlete2\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class LazyLoad extends AbstractHelper
{

	/**
	 *
	 * @var ObjectManager
	 */
	public $objectManager;
	protected $_lazyExcludeClass;
	protected $isEnabled;

	public function isEnabled()
	{
		if (is_null($this->isEnabled)) {
			$this->isEnabled = (bool)$this->getConfig('athlete2_settings/general/lazyload');
		}

		return $this->isEnabled;
	}

	public function getConfig($path, $storeCode = null)
	{
		return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function _filterImg($html = '')
	{
		if (preg_match('@(data-original=\"|lazy)@i', $html)) {
			return false;
		}
		$class = $this->getExcludeClass();
		if (!empty($class) && preg_match('@class="([^"]+)"@i', $html, $matches)) {
			$matches = array_filter(explode(' ', $matches[1]));
			$intersect = array_intersect($class, $matches);
			return empty($intersect);
		}
		return true;
	}

	public function getExcludeClass()
	{
		if (empty($this->_lazyExcludeClass)) {
			$class = $this->getConfig('athlete2_settings/general/lazyload_exclude');
			if (empty($class)) {
				$class = ['rev-slidebg'];
			} elseif (preg_match_all('@\S+@', $class, $matches)) {
				$class = array_filter($matches[0]);
			}
			if (is_array($class) && !empty($class)) {
				$this->_lazyExcludeClass = $class;
			}
		}

		return $this->_lazyExcludeClass;
	}

	public function replaceImageToLazy($html = '')
	{
		if ($this->isEnabled() && preg_match_all('@<img[^<>]+src\=[^<>]+>@ims', $html, $matches)) {
			$matches = array_filter($matches[0], [$this, '_filterImg']);
			if (!empty($matches) && is_array($matches)) {
				$svg = $this->getViewFileUrl('Olegnax_Core/images/preloader-img.svg');
				$_matches = [];
				foreach ($matches as $htmlImg) {
					$_htmlImg = preg_replace('@src="([^\"]+)"@im', 'src="' . $svg . '" data-original="$1"', $htmlImg);
					$_htmlImg = preg_replace('@class="([^\"]+)"@im', 'class="$1 lazy"', $_htmlImg);
					if (!preg_match('@class="@i', $_htmlImg)) {
						$_htmlImg = preg_replace('@<img@im', '$0 class="lazy"', $_htmlImg);
					}
					$_matches[$htmlImg] = $_htmlImg;
				}
				if (!empty($_matches)) {
					$search = array_keys($_matches);
					$replace = array_values($_matches);
					$html = str_replace($search, $replace, $html);
				}
			}
		}

		return $html;
	}

	/**
	 * Retrieve url of a view file
	 *
	 * @param string $fileId
	 * @param array $params
	 * @return string
	 */
	public function getViewFileUrl($fileId, array $params = [])
	{
		try {
			$params = array_merge(['_secure' => $this->_loadObject(RequestInterface::class)->isSecure()], $params);
			return $this->_loadObject(Repository::class)->getUrlWithParams($fileId, $params);
		} catch (LocalizedException $e) {
			return $this->_getNotFoundUrl();
		}
	}

	protected function _loadObject( $object ) {
		return $this->_getObjectManager()->get( $object );
	}

	protected function _getObjectManager()
	{
		return ObjectManager::getInstance();
	}

	/**
	 * Get 404 file not found url
	 *
	 * @param string $route
	 * @param array $params
	 * @return string
	 */
	protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
	{
		return $this->getUrl($route, $params);
	}

	/**
	 * Generate url by route and parameters
	 *
	 * @param string $route
	 * @param array $params
	 * @return  string
	 */
	public function getUrl($route = '', $params = [])
	{
		return $this->_loadObject(UrlInterface::class)->getUrl($route, $params);
	}
}