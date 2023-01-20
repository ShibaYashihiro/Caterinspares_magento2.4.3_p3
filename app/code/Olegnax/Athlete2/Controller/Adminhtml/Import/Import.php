<?php

namespace Olegnax\Athlete2\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use \Olegnax\Athlete2\Model\DynamicStyle\Generator as DynamicStyleGenerator;
use \Magento\Store\Model\ScopeInterface;

class Import extends \Magento\Backend\App\Action {

    const ADMIN_RESOURCE = 'Olegnax_Athlete2::import';
	const DEMO_DIR		 = 'code/Olegnax/Athlete2/Demos';

	protected $_filesystem;
	private $_demo;

	protected $_storeManager;

	/**
	 * Dynamic Style generator
	 *
	 * @var \Olegnax\Athlete2\Model\DynamicStyle\Generator
	 */
	protected $_DynamicStyleGenerator;

	/**
	 * Constructor
	 */
	public function __construct(
	\Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem,
 \Magento\Framework\Xml\Parser $parser, DynamicStyleGenerator $generator
	) {
		$this->_filesystem				 = $filesystem;
		$this->_parser					 = $parser;
		$this->_DynamicStyleGenerator	 = $generator;
		parent::__construct( $context );
	}

	public function execute() {
		$subDir		 = $this->getRequest()->getParam( 'subdir', '' );
		$this->_demo = $this->getRequest()->getParam( 'demo' );
		if ( !empty( $this->_demo ) && $this->demoFileExists( $this->_demo, $subDir ) ) {
			if ( $this->demoIsReadable( $this->_demo, $subDir ) ) {
				$demoPath = $this->getDemoPath( $this->_demo, $subDir );
				try {
					$xmlArray = $this->_parser->load( $demoPath )->xmlToArray();
					if ( is_array( $xmlArray ) && !empty( $xmlArray ) && isset( $xmlArray[ 'root' ] ) ) {
						foreach ( $xmlArray[ 'root' ] as $key => $value ) {
							$methodName = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', strtolower( $key ) ) ) );
							if ( method_exists( $this, $methodName ) ) {
								call_user_func( [ $this, $methodName ], $value );
							}
						}
						$this->clearCache();
						$this->messageManager->addSuccess( __( '%1 was successfully imported.', $this->convertString( $this->_demo ) ) );
					}
				} catch ( \Exception $e ) {
					$this->messageManager->addError( $e->getMessage() );
				}
			} else {
				$this->messageManager->addError( __( 'Cannot import this Demo.' ) );
			}
		} else {
			$this->messageManager->addError( __( 'This Demo no longer exists.' ) );
		}

		return $this->_redirect( $this->_redirect->getRefererUrl() );
	}

	protected function getDemoPath( $demoId, $subDir = '' ) {
		if ( !empty( $subDir ) ) {
			$subDir = $subDir . DIRECTORY_SEPARATOR;
		} else {
			$subDir = '';
		}
		return $this->getAbsolutePath( self::DEMO_DIR ) . DIRECTORY_SEPARATOR . $subDir . $demoId . '.xml';
	}

	protected function demoIsReadable( $demoId, $subDir = '' ) {
		$demoPath = $this->getDemoPath( $demoId, $subDir );

		return is_readable( $demoPath );
	}

	protected function demoFileExists( $demoId, $subDir = '' ) {
		$demoPath = $this->getDemoPath( $demoId, $subDir );

		return file_exists( $demoPath );
	}

	protected function getAbsolutePath( $path ) {
		return $this->_filesystem->getDirectoryRead( DirectoryList::APP )->getAbsolutePath( $path );
	}

	protected function _getObjectManager() {
		return \Magento\Framework\App\ObjectManager::getInstance();
	}

	protected function _loadObject( $object ) {
		return $this->_getObjectManager()->get( $object );
	}

	public function convertString( $demoId ) {
		return ucwords( strtolower( str_replace( [ '-', '_', '\\', '/' ], ' ', $demoId ) ) );
	}

	public function clearCache() {
		$cacheTypeList		 = $this->_loadObject( '\Magento\Framework\App\Cache\TypeListInterface' );
		$CacheFrontendPool	 = $this->_loadObject( '\Magento\Framework\App\Cache\Frontend\Pool' );
		$types				 = array( 'config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice' );
		foreach ( $types as $type ) {
			$cacheTypeList->cleanType( $type );
		}
		foreach ( $CacheFrontendPool as $cacheFrontend ) {
			$cacheFrontend->getBackend()->clean();
		}
	}

	public function setConfig( $demoContent ) {
		if ( is_array( $demoContent ) && !empty( $demoContent ) ) {
			$website = $this->getRequest()->getParam( 'website' );
			$store	 = $this->getRequest()->getParam( 'store' );
			$config	 = $this->_loadObject( '\Magento\Config\Model\Config\Factory' );
			foreach ( $demoContent as $section => $groups ) {
				$configData = [
					'section'	 => $section,
					'website'	 => $website,
					'store'		 => $store,
					'groups'	 => $this->_prepareGroup( $groups, $section ),
				];
				$config->create( [ 'data' => $configData ] )->save();

				$this->_eventManager->dispatch( 'admin_system_config_save', [
					'configData' => $configData,
					'request'	 => $this->getRequest()
				] );
			}
		}
	}

	private function _setConfigImage( $fieldPath, $value ) {
		$store	 = (int) $this->getRequest()->getParam( 'store', 0 );
		$website = (int) $this->getRequest()->getParam( 'website', 0 );
		$config	 = $this->_loadObject( '\Magento\Framework\App\Config\ConfigResource\ConfigInterface' );
		if ( is_array( $fieldPath ) ) {
			$fieldPath = implode( '/', $fieldPath );
		}
		if ( $website ) {
			$config->saveConfig( $fieldPath, $value, ScopeInterface::SCOPE_WEBSITES, $website );
		} elseif ( $store ) {
			$config->saveConfig( $fieldPath, $value, ScopeInterface::SCOPE_STORES, $store );
		} else {
			$config->saveConfig( $fieldPath, $value );
		}
	}

	public function _prepareGroup( $content, $section ) {
		$groups = [];
		foreach ( $content as $group_name => $group_fields ) {
			$fields = [];
			foreach ( $group_fields as $field => $value ) {
				if ( is_null( $value ) ) {
					$value = '';
				}
				if ( !is_string( $value ) ) {
					continue;
				}
				if ( preg_match( '/\.[a-z0-9]{3,4}$/i', $value ) ) {
					$this->_setConfigImage( [ $section, $group_name, $field ], $value );
				}
				$fields[ $field ] = [ 'value' => $value ];
			}
			$groups[ $group_name ] = [
				'fields' => $fields,
			];
		}

		return $groups;
	}

	public function setBlocks( $demoContent ) {
		if ( is_array( $demoContent ) && isset( $demoContent[ 'item' ] ) && !empty( $demoContent[ 'item' ] ) ) {
			if ( isset( $demoContent[ 'item' ][ 'identifier' ] ) ) {
				$demoContent = [ $demoContent[ 'item' ] ];
			} else {
				$demoContent = $demoContent[ 'item' ];
			}
			$model = $this->_loadObject( '\Magento\Cms\Model\Block' );
			foreach ( $demoContent as $item ) {
				if ( !array_key_exists( 'identifier', $item ) || empty( $item[ 'identifier' ] ) ) {
					continue;
				}

				$staticBlocksCollection = $model->getCollection()->addFieldToFilter( 'identifier', $item[ 'identifier' ] )->load();
				if ( !empty( $staticBlocksCollection ) ) {
					foreach ( $staticBlocksCollection as $_block ) {
						$_block->delete();
					}
				}
				$model->setData( $item )->setIsActive( 1 )->setStores( array( 0 ) )->save();
			}
		}
	}

	public function setPages( $demoContent ) {
		if ( is_array( $demoContent ) && isset( $demoContent[ 'item' ] ) && !empty( $demoContent[ 'item' ] ) ) {
			if ( isset( $demoContent[ 'item' ][ 'identifier' ] ) ) {
				$demoContent = [ $demoContent[ 'item' ] ];
			} else {
				$demoContent = $demoContent[ 'item' ];
			}
			$model = $this->_loadObject( '\Magento\Cms\Model\Page' );
			foreach ( $demoContent as $item ) {
				if ( !array_key_exists( 'identifier', $item ) || empty( $item[ 'identifier' ] ) ) {
					continue;
				}

				$staticBlocksCollection = $model->getCollection()->addFieldToFilter( 'identifier', $item[ 'identifier' ] )->load();
				if ( !empty( $staticBlocksCollection ) ) {
					foreach ( $staticBlocksCollection as $_block ) {
						$_block->delete();
					}
				}
				$model->setData( $item )->setIsActive( 1 )->setStores( array( 0 ) )->save();
			}
		}
	}

	public function setBannersliders( $demoContent ) {
		if ( is_array( $demoContent ) && isset( $demoContent[ 'item' ] ) && !empty( $demoContent[ 'item' ] ) ) {
			if ( isset( $demoContent[ 'item' ][ 'group_name' ] ) ) {
				$demoContent = [ $demoContent[ 'item' ] ];
			} else {
				$demoContent = $demoContent[ 'item' ];
			}
			$model	 = $this->_loadObject( '\Olegnax\BannerSlider\Model\Group' );
			$model2	 = $this->_loadObject( '\Olegnax\BannerSlider\Model\Slides' );

			foreach ( $demoContent as $item ) {
				if ( !array_key_exists( 'group_name', $item ) ) {
					continue;
				}
				$groupCollection = $model->getCollection()->addFieldToFilter( 'identifier', $item[ 'identifier' ] )->load();
				if ( !empty( $groupCollection ) ) {
					foreach ( $groupCollection as $_group ) {
						$slidesCollection = $model2->getCollection()->addFieldToFilter( 'slide_group', $_group->getId() )->load();
						if ( !empty( $slidesCollection ) ) {
							foreach ( $slidesCollection as $_slides ) {
								$_slides->delete();
							}
						}
						$_group->delete();
					}
				}

				$slides = [];
				if ( array_key_exists( 'slides', $item ) ) {
					if ( !empty( $item[ 'slides' ] ) && array_key_exists( 'item', $item[ 'slides' ] ) ) {
						$slides = $item[ 'slides' ][ 'item' ];
					}
					unset( $item[ 'slides' ] );
				}
				$current_model	 = $model->setData( $item )->save();
				if ( !empty( $slides ) && $group_id		 = $current_model->getId() ) {
					foreach ( $slides as $_item ) {
						$model2->setData( $_item )->setData( 'status', 1 )->setData( 'store_id', 0 )->setData( 'slide_group', $group_id )->save();
					}
				}
			}
		}
	}

}
