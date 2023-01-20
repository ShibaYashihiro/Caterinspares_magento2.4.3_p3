<?php 
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Helper; 

use Magento\Framework\App\RequestInterface;
use \Magento\Framework\Registry;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory as PartFinderUniversalProductsFactory;
use Mageants\PartFinder\Helper\Data as PartFinderHelper;
	
class PartFinderProducts extends \Magento\Framework\App\Helper\AbstractHelper 
{
	/**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory 
     */
    protected $_partFinderOptionValuesCollectionFactory;
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory 
     */
    protected $_partFinderOptionValueMapCollectionFactory;
	/**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory
     */
    protected $_partFinderUniversalProductsFactory;
    /**
     * @var ConfigInterface
     */
    protected $_request;
	  /**
     * @var _partFinderHelper 
     */
    protected $_partFinderHelper;
	  /**
     * @var _product_ids
     */
    private $_product_ids = [];
	
	  /**
     * @var _product_ids
     */
    private $_uni_product_ids = [];
	/**
     * @param RequestInterface $request
     * @param PartFinderOptionValuesCollectionFactory $partFinderOptionValuesCollectionFactory
     * @param PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory
     * @param PartFinderUniversalProductsFactory $partFinderUniversalProductsFactory
     * @param Registry $coreRegistry
     * @param Data $partFinderHelper
     */
    public function __construct(
        RequestInterface $request,
		PartFinderOptionValuesCollectionFactory $partFinderOptionValuesCollectionFactory,
		PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory,
		PartFinderUniversalProductsFactory $partFinderUniversalProductsFactory,
		Registry $coreRegistry,
		PartFinderHelper $partFinderHelper
    ) {
        $this->_request = $request;
		
        $this->_partFinderHelper = $partFinderHelper;
		
		$this->_coreRegistry = $coreRegistry; 
		
        $this->_partFinderOptionValuesCollectionFactory = $partFinderOptionValuesCollectionFactory;
		
        $this->_partFinderOptionValueMapCollectionFactory = $partFinderOptionValueMapCollectionFactory;
		
		$this->_partFinderUniversalProductsFactory = $partFinderUniversalProductsFactory;
    }
	
	/**
     * @return array
    */	 
	public function getProductIds()
	{
		$product_ids = [];
		
		$value_id = $this->getValueId();
		
		if($value_id && $value_id!="")
		{
			$this->initProductIds($value_id);
			
			$this->initUniversalProductIds($value_id);
			
			if(isset($this->_product_ids[$value_id]))
			{
				 $product_ids = $this->_product_ids[$value_id];
			} 
			
			 if(isset($this->_uni_product_ids[$value_id]))
			 {
				 $product_ids = array_merge($product_ids,$this->_uni_product_ids[$value_id]);
			 }				
		}
		else{
			$product_ids = $this->getPartFinderOptionValueMapCollection();
		}
		
		return array_unique($product_ids);
	}
	/**
     * @return 
    */	 
	private function initProductIds($value_id)
	{	
		if(!isset($this->_product_ids[$value_id]))
		{
			$map_value_ids = $this->getOptionValueChildren([$value_id]);

			$partFinderOptionValueMapCollection = $this->_partFinderOptionValueMapCollectionFactory->create();
			$partFinderOptionValueMapCollection
				->addFieldToSelect("product_id")
				->addFieldToFilter("value_id",["in"=>$map_value_ids]);
				
			foreach($partFinderOptionValueMapCollection as $value_map)
			{
				$product_ids[] = $value_map->getProductId();
			}
			
			$this->_product_ids[$value_id] = $product_ids;
		}
	}
	
	/**
     * @return 
    */	 
	private function initUniversalProductIds($value_id)
	{
		$product_ids = [];
		
		$helper = $this->_partFinderHelper;
		
		if(!isset($this->_uni_product_ids[$value_id]))
		{
			if($helper->getAllowUniversalProdConfig())
			{
				$partfinder = $this->_coreRegistry->registry('mageants_partfinders');
				
				$partFinderUniversalProductsFactory = $this->_partFinderUniversalProductsFactory->create();

				$partFinderUniversalProductsFactory
					->addFieldToSelect("product_id")
					->addFieldToFilter("finder_id",$partfinder->getId());
				
				foreach($partFinderUniversalProductsFactory as $uni_prod)
				{
					$product_ids[] = $uni_prod->getProductId();
				}
				
				$this->_uni_product_ids[$value_id] = $product_ids;
			}				
		}
		else
		{
			if(!$helper->getAllowUniversalProdConfig())
			{
				unset($this->_uni_product_ids[$value_id]);
			}
		}
	
		return $product_ids;
	}
	/**
     * @return int
     */
	private function getValueId()
	{
		$value_id = false;
		
		$param = $this->_request->getParam(PartFinderHelper::FINDER_KEY);
		
		if($param && $param!="")
		{
			$finder_query = explode("-",$param);
			
			$value_id = (int)$finder_query[count($finder_query)-1];
		}
		
		return $value_id;
	}
	/**
     * @param value_ids 
     * @return array
     */
	private function getOptionValueChildren($value_ids= [])
	{
		$partFinderOptionValuesCollection = $this->_partFinderOptionValuesCollectionFactory->create();
		
		$partFinderOptionValuesCollection
			->addFieldToSelect(["id"])
			->addFieldToFilter("parent_id",["in"=>$value_ids]);
		
		if(!$partFinderOptionValuesCollection->getSize())
		{
			return $value_ids;
		}
		else
		{
			return $this->getOptionValueChildren( $partFinderOptionValuesCollection->getAllIds() );
		}
	}
	public function getPartFinderOptionValueMapCollection(){
		$partFinderOptionValueMapCollection = $this->_partFinderOptionValueMapCollectionFactory->create();
		foreach($partFinderOptionValueMapCollection as $collection){
			$allProductIds[] = $collection->getProductId();
		}
		return $allProductIds;
	}
}