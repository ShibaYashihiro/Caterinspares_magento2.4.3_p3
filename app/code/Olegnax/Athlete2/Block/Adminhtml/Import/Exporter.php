<?php

namespace Olegnax\Athlete2\Block\Adminhtml\Import;

use \Magento\Store\Model\ScopeInterface;

class Exporter extends \Olegnax\Athlete2\Block\Adminhtml\Import {

	const DEMO_EXPORT_PATH = '*/*/export';

	protected $scopeConfig;
	protected $blockFactory;
	protected $pageFactory;
	protected $storeManager;
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \Magento\Backend\Block\Template\Context  $context
	 * @param array $data
	 */
	public function __construct(
	\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Filesystem $filesystem,
 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
 \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockFactory,
 \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageFactory,
 \Magento\Store\Model\StoreManagerInterface $storeManager, \Olegnax\Athlete2\Helper\Helper $helper, array $data = []
	) {
		$this->scopeConfig	 = $scopeConfig;
		$this->blockFactory	 = $blockFactory;
		$this->pageFactory	 = $pageFactory;
		$this->storeManager	 = $storeManager;
		$this->helper		 = $helper;
		parent::__construct( $context, $filesystem, $data );
	}

	protected function getStoreIds() {
		$storeIds = [];

		if ( $storeId = (int) $this->getRequest()->getParam( 'store' ) ) {
			$storeIds[] = $storeId;
		} elseif ( $websiteId = (int) $this->getRequest()->getParam( 'website' ) ) {
			$stores = $this->storeManager->getWebsite( $websiteId )->getStores();
			foreach ( $stores as $_store ) {
				$storeIds[] = $_store->getId();
			}
		}

		return $storeIds;
	}

	public function getConfig() {
		if ( $websiteId = $this->getRequest()->getParam( 'website' ) ) {
			return $this->scopeConfig->getValue( '', ScopeInterface::SCOPE_WEBSITE, $websiteId );
		}
		if ( $storeId = $this->getRequest()->getParam( 'store' ) ) {
			return $this->scopeConfig->getValue( '', ScopeInterface::SCOPE_STORE, $storeId );
		}
		return $this->scopeConfig->getValue( '' );
	}

	public function getBlocks() {
		$storeIds		 = $this->getStoreIds();
		$itemCollection	 = $this->blockFactory->create();
		$items			 = [];
		if ( !empty( $storeIds ) ) {
			$storeIds[] = 0;
			$itemCollection->addFieldToFilter( 'store_id', [ 'in' => [ $storeIds ] ] );
		}
		if ( count( $itemCollection ) > 0 ) {
			foreach ( $itemCollection as $item ) {
				$items[ $item->getIdentifier() ] = $item->getTitle();
			}
		}
		return $items;
	}

	public function getPages() {
		$storeIds		 = $this->getStoreIds();
		$itemCollection	 = $this->pageFactory->create();
		if ( !empty( $storeIds ) ) {
			$storeIds[] = 0;
			$itemCollection->addFieldToFilter( 'store_id', [ 'in' => [ $storeIds ] ] );
		}
		$items = [];
		if ( count( $itemCollection ) > 0 ) {
			foreach ( $itemCollection as $item ) {
				$items[ $item->getIdentifier() ] = $item->getTitle();
			}
		}
		return $items;
	}

	public function getBannerSlider() {
		$items = [];
		if ( $this->helper->isActivePlugin( 'Olegnax_BannerSlider' ) ) {
			$group			 = \Magento\Framework\App\ObjectManager::getInstance()->get( '\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory' )->create();
			$itemCollection	 = $group->addFieldToSelect( '*' );
			if ( count( $itemCollection ) > 0 ) {
				foreach ( $itemCollection as $item ) {
					$items[ $item->getIdentifier() ] = $item->getGroupName();
				}
			}
		}
		return $items;
	}

	public function actionExport( array $subArguments = [] ) {
		$arguments	 = [];
		if ( $storeId	 = $this->getRequest()->getParam( 'store' ) ) {
			$arguments[ 'store' ] = $storeId;
		} elseif ( $websiteId = $this->getRequest()->getParam( 'website' ) ) {
			$arguments[ 'website' ] = $websiteId;
		}
		if ( is_array( $subArguments ) ) {
			$arguments = array_merge( $arguments, $subArguments );
		}

		$url = $this->getUrl( self::DEMO_EXPORT_PATH, $arguments );

		return $url;
	}

}
