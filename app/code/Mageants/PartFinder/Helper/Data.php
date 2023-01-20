<?php 
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Helper; 

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Helper\Context;
use Magento\Email\Model\Template as EmailTemplate;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Registry;
use \Mageants\PartFinder\Model\PartFinderOptionsFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory;
use \Mageants\PartFinder\Model\PartFindersFactory;

		
class Data extends \Magento\Framework\App\Helper\AbstractHelper 
{
    /**
     * PartFindersFactory Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFinders
     */
    protected $_partFindersFactory;
    /**
     * PartFinderOptionValues Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptionValuesFactory
     */
    protected $_partFinderOptionValuesFactory;
	/**
     * PartFinderOptions Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptions
     */
    protected $_partFinderOptionsFactory;
    /**
     * @var ConfigInterface
     */
    protected $_request;
    /**
     * Core Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	/** 
	* @var \Magento\Framework\App\Config\ScopeConfigInterfac 
	*/ 
	protected $_scopeConfig;
	/** 
	* @var array
	*/ 
	protected $_partfinderConfig; 
	
	/** 
	* @var \Magento\Backend\Helper\Data
	*/ 
	protected $_helperBackend; 
	/** 
	* @var \Magento\Backend\Model\UrlInterface
	*/ 
	protected $_url; 
	/** 
	* Finder Url Parma
	*/ 
	CONST FINDER_KEY = 'findpart'; 
	/** 
	* Finder Default Route Name
	*/ 
	CONST FRONTEND_DEFAULT_ROUTE_NAME = 'partfinder'; 

	/*Extention Config Constants*/
	CONST ENABLE = 'mageants_partfinder/general/enable'; 
	CONST DISPLAY_BTN = 'mageants_partfinder/general/display_btn'; 
	CONST ALLOW_UNIVERSAL_PROD = 'mageants_partfinder/general/allow_universal_prod'; 
	CONST APPLY_FILTER_ON_CURR_CAT = 'mageants_partfinder/general/apply_filter_on_curr_cat'; 
	CONST CUSTOM_RESULT_URL = 'mageants_partfinder/general/custom_result_url'; 
	CONST AUTO_START_SEARCH = 'mageants_partfinder/general/auto_start_search'; 
	CONST RESET_ON_HOME = 'mageants_partfinder/general/reset_on_home'; 
	CONST CLEAR_FINDER_SESSION = 'mageants_partfinder/general/clear_finder_session'; 
	CONST HISTORY_LYF_TIME = 'mageants_partfinder/import/history_lyf_time'; 
	CONST MAX_ROW_IMPORT = 'mageants_partfinder/import/max_row_import'; 
	 /**
	 *	construct
	 *
     * @param Context $context,
	 * @param ScopeConfigInterface $scopeConfig 
	 * @param Data $HelperBackend
     */
	public function __construct( 
		Context $context, 
		\Magento\Backend\Helper\Data $HelperBackend,
		Registry $coreRegistry,
		PartFinderOptionsFactory $partFinderOptionsFactory,
		PartFinderOptionValuesFactory $partFinderOptionValuesFactory,
		PartFindersFactory $partFindersFactory
	) 
	{
		$this->_request 	= $context->getRequest();
		
		$this->_partFindersFactory           = $partFindersFactory;
		
        $this->_partFinderOptionsFactory           = $partFinderOptionsFactory;
        
		$this->_partFinderOptionValuesFactory           = $partFinderOptionValuesFactory;
		
        $this->_url = $context->getUrlBuilder();

		$this->_scopeConfig = $context->getScopeConfig();
		
		$this->_helperBackend = $HelperBackend;		
		
		$this->_coreRegistry = $coreRegistry;
    }
	
	/**
     * Retrieve extention enable or disable
     *
     * @return boolean
     */
    public function isExtentionEnable()
	{
		return $this->getConfig(Self::ENABLE);
    }
	/**
     * @return string
     */
    public function getDisplayButtonConfig()
	{
		return $this->getConfig(Self::DISPLAY_BTN);
    }
	
	/**
     * @return boolean
     */
    public function getAllowUniversalProdConfig()
	{
		return $this->getConfig(Self::ALLOW_UNIVERSAL_PROD);
    }
	
	/**
     * @return boolean
     */
    public function getApplyFilterOnCurrCat()
	{
		return $this->getConfig(Self::APPLY_FILTER_ON_CURR_CAT);
    }
	
	/**
     * @return string
     */
    public function getCustomResultUrl()
	{
		return $this->getConfig(Self::CUSTOM_RESULT_URL);
    }
	
	/**
     * @return boolean
     */
    public function getAutoStartSearch()
	{
		return $this->getConfig(Self::AUTO_START_SEARCH);
    }
	
	/**
     * @return boolean
     */
    public function getResetInHome()
	{
		return $this->getConfig(Self::RESET_ON_HOME);
    }
	
	/**
     * @return boolean
     */
    public function getClearFinderSession()
	{
		return $this->getConfig(Self::CLEAR_FINDER_SESSION);
    }
	
	/**
     * @return int
     */
    public function getMaxRowImport()
	{
		return $this->getConfig(Self::MAX_ROW_IMPORT);
    }
	
	/**
     * @return boolean
     */
    public function getHistoryLyfTime()
	{
		return $this->getConfig(Self::HISTORY_LYF_TIME);
    }
	
	/**
     * Retrieve extention system configuration
     *
     * @return boolean
     */
    public function getConfig($config_path)
	{
		return $this->_scopeConfig->getValue( $config_path,ScopeInterface::SCOPE_STORE);
    }
		
	/**
     * Retrieve getProductsGridUrl
     *
     * @return string
     */
    public function getProductsGridUrl()
	{
		 return $this->_helperBackend->getUrl('mageants_partfinder/partfinders/products/ajax/1/', ['_current' => true]);
    }
		
	/**
     * Retrieve getHistoryGridUrl
     *
     * @return string
     */
    public function getHistoryGridUrl()
	{
		 return $this->_helperBackend->getUrl('mageants_partfinder/partfinders/history/ajax/1/', ['_current' => true]);
    }
		
	/**
     * Retrieve getUnivarsalProductsUrl
     *
     * @return string
     */
    public function getUnivarsalProductsUrl()
	{
		 return $this->_helperBackend->getUrl('mageants_partfinder/partfinders/universalProducts/ajax/1/', ['_current' => true]);
    }
		
	/**
     * Retrieve serialize setting
     *
     * @return array
     */
    public function serializeSetting($data)
	{
		 return serialize($data);
    }

    public function getParentId($id)
	{
		 return serialize($data);
    }
	
	/**
     * set Part Finder Registry
     *
     * @return 
     */
    public function setPartFinderRegistry($partfinder = null)
	{
		if(!$this->_coreRegistry->registry('mageants_partfinders'))
		{
			 if(!$partfinder)
			 {
				 $value_id = false;
		
				$param = $this->_request->getParam(Self::FINDER_KEY);
				
				if($param && $param!="")
				{
					$finder_query = explode("-",$param);
					
					$value_id = (int)$finder_query[count($finder_query)-1];
				}
				
				if($value_id)
				{
					$option_id = $this->_partFinderOptionValuesFactory->create()->load($value_id)->getOptionId();
					
					$finder_id = $this->_partFinderOptionsFactory->create()->load($option_id)->getFinderId();
					
					$partfinder = $this->_partFindersFactory->create()->load($finder_id);					
				}					
			 }
			 if($partfinder)
			 {
				$this->_coreRegistry->register('mageants_partfinders', $partfinder);
			 }
		}
    }
	
	/**
     * Retrieve unserialize setting
     *
     * @return array
     */
    public function unserializeSetting($string)
	{
		$data = [];
		
		if(!empty($string))
		{
			$data = unserialize($string);
		}
		
		return $data;
    }
}