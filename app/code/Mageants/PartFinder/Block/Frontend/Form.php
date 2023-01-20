<?php
 /**
 * @category Mageants Advancesizechart
 * @package Mageants_Advancesizechart
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Frontend;
 
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\PartFinder\Helper\Data as PartfinderHelper;
use \Mageants\PartFinder\Model\PartFindersFactory;
use \Mageants\PartFinder\Model\Source\FinderTemplates;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;
use Magento\Framework\Registry;

/**
 * Class BlockRepository
 *
 * @package Mageants\PartFinder\Block\Frontend
 */
class Form extends \Magento\Framework\View\Element\Template
{
	const DEFAULT_TEMPLATE = "Mageants_PartFinder::default_form.phtml";
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
		Context $context,
		array $data = [],
		Registry $registry,
		PartfinderHelper $helper,
		PartFindersFactory $partfindersFactory,
		PartFinderOptionsCollectionFactory $partFinderOptionsCollectionFactory
	) {	
		parent::__construct($context, $data);
		
		$this->_helper = $helper;
		
		$this->registry = $registry;
		if(isset($data["finder_from_search_result"]) && $helper->isExtentionEnable())
		{
			$this->_parfFinder = $this->registry->registry("mageants_partfinders");

			if(!method_exists($this->_parfFinder,'getId')){
				return false;
			}
			
			$data["id"] =$this->_parfFinder->getId();

		}
		
		if(isset($data["id"]) && $helper->isExtentionEnable())
		{
			$this->finder_id = $data["id"];
			
			$this->_partfindersFactory = $partfindersFactory;
			
			$this->_partFinderOptionsCollectionFactory = $partFinderOptionsCollectionFactory;
			$this->_parfFinder = $this->_initPartFinder();
			
			if($this->_parfFinder->getId() && $this->_parfFinder->getStatus())
			{
				$this->setTemplate(Self::DEFAULT_TEMPLATE);				
			}
		}
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
	private function _initPartFinder()
	{
		$partfinder = $this->_parfFinder;
		
		if(!$partfinder)
		{
			$partfinder = $this->_partfindersFactory->create()->load($this->finder_id);			
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