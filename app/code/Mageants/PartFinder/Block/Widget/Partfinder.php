<?php 
namespace Mageants\PartFinder\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\PartFinder\Helper\Data as PartfinderHelper;
use \Mageants\PartFinder\Model\PartFindersFactory;
use \Mageants\PartFinder\Model\Source\FinderTemplates;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;
use Magento\Framework\Registry;
 
class Partfinder extends Template implements BlockInterface {

	/*protected $_template = "widget/partfinder.phtml";*/
	const DEFAULT_RESULT_URL_KEY = "partfinder/result/";
	const PARTFINDER_OPTION_URL_KEY = "partfinder/index/option/";
	/**
     * \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionCollectionFactory ;
	/**
     * @var _storeManager
     */
	protected $_storeManager;
	/**
     * @var _objectManager
     */
	protected $_objectManager;
	/**
     * PartFinders  Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFindersFactory
     */
    protected $_partfindersFactory;
	/**
     * PartFinder Helper
     * 
     * @var \Mageants\PartFinder\Helper\Data
     */
    protected $_helper;
	/**
     * @var _finder_id
     */
    protected $_finder_id = false;
	/**
     * @var _parfFinder
     */
    protected $_parfFinder = false;
	/**
     * @var _finderOptions
     */
    protected $_finderOptions = [];
    /**
     * @param Context $context,
	 * @param array $data = [],
	 * @param Data $helper,
	 * @param PartFindersFactory $partfindersFactory
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        Registry $registry,
		PartfinderHelper $helper,
		PartFindersFactory $partfindersFactory,
		PartFinderOptionsCollectionFactory $partFinderOptionsCollectionFactory,
		\Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);

        $this->_helper = $helper;
		
		$this->registry = $registry;

		$this->moduleManager = $moduleManager;
		
		if($helper->isExtentionEnable())
		{
			$this->_partfindersFactory = $partfindersFactory;
			$this->_partFinderOptionsCollectionFactory = $partFinderOptionsCollectionFactory;
			$this->_parfFinder = "";
		}

    }

    public function getPartFinderData($id){
    	if ($this->moduleManager->isOutputEnabled('Mageants_PartFinder')) {
		    if($id && $this->_helper->isExtentionEnable())
			{
				$this->_parfFinder = $this->_initPartFinder($id);
				if($this->_parfFinder->getData('status')){
					return true;
				}else{
					return false;
				}
			}
			return false;
		}
		return false;
    }
 
    /**
     * construct function
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/partfinder.phtml');
    }
 
    public function getPartFinderId()
    {
        return $this->getData('widpartfinder');
    }

    /**
     * Retrieve Module PartFinder Template Class
     *
     * @return string 
     */
	public function getFinderTemplateClass()
	{
		$finder_opt_count = $this->getFinderOption()->count();
		
		$class = " partfinder-".$this->_parfFinder->getId()."  finder-options-count-".$finder_opt_count ;
		
		switch($this->_parfFinder->getFinderTemplate())
		{
						
			case FinderTemplates::STATUS_VERTICAL:
				$class .= " partfinder-vertical";
			break;
			case FinderTemplates::STATUS_HORIZONTAL:
			default :
				$class .= " partfinder-horizontal";
			break;
		}
		
		return $class;
	}
	/**
     * Retrieve Module PartFinder
     *
     * @return _initPartFinder
     */
	private function _initPartFinder($id)
	{
		$partfinder = $this->_parfFinder;
		
		if(!$partfinder)
		{
			$partfinder = $this->_partfindersFactory->create()->load($id);			
		}
		
		if($partfinder->getId())
		{
			$this->_finderOptions = $this->_partFinderOptionsCollectionFactory->create()->addFieldToFilter("finder_id",$partfinder->getId())->setOrder("sort_order","ASC");
		}
		
		return $partfinder;
	}
	/**
     * Retrieve PartFinder Form Action
     *
     * @return getFormAction
     */
	public function getFormAction()
	{
		$form_action = "";
		
		if($this->_request->getFullActionName() == "catalog_category_view" && $this->_helper->getApplyFilterOnCurrCat())
		{
			$category = $this->registry->registry('current_category');
			
			$form_action =  $category->getUrl();
		}
		else if($this->_parfFinder->getSearchResultUrl() != "")
		{
			$form_action = $this->getUrl($this->_parfFinder->getSearchResultUrl() );
		}
		else if($this->_helper->getCustomResultUrl() != "")
		{
			$form_action = $this->getUrl($this->_helper->getCustomResultUrl() );
		}
		else
		{
			$form_action = $this->getUrl(Self::DEFAULT_RESULT_URL_KEY);
		}
		
		return $form_action;
	}
	/**
     * Retrieve PartFinder
     *
     * @return getPartFinder
     */
	public function getFinderOptionUrl($opt_id = 0)
	{
		$param = $this->getRequest()->getParam(PartFinderHelper::FINDER_KEY);
		
		return $this->getUrl(Self::PARTFINDER_OPTION_URL_KEY,['id'=>$opt_id,PartFinderHelper::FINDER_KEY=>$param]);
	}
	/**
     * Retrieve PartFinder
     *
     * @return getPartFinder
     */
	public function getPartFinder()
	{
		return $this->_parfFinder;
	}
	/**
     * Retrieve PartFinder Options
     *
     * @return getFinderOption
     */
	public function getFinderOption()
	{
		return $this->_finderOptions;
	}
	/**
     * Retrieve PartFinder Data Helper
     *
     * @return _helper
     */
	public function getPartFinderHelper()
	{
		return $this->_helper;
	}
	/**
     * Retrieve current Store Id 
     *
     * @return store_id
     */
	public function getCurrentStoreId(){
		return $this->_storeManager->getStore()->getId();
	}
}