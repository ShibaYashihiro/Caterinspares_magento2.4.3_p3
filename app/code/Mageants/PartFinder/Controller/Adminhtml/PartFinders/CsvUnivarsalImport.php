<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\PartFinder\Model\PartFinderUniversalProductsFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\File\Csv;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class CsvUnivarsalImport extends \Magento\Backend\App\Action
{
 	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_csv_universal_import';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
	/**
     * PartFinderImportFilesFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory
     */
    protected $_partFinderUniversalProductsFactory;
	/**
     * Image model
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    /**
     * Csv
     * 
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvProcessor;
    /**
     * constructor
     * 
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
		JsonFactory $jsonFactory,
		PartFinderUniversalProductsFactory $partFinderUniversalProductsFactory,		
		DateTime $datetime,
		ProductCollectionFactory $productCollectionFactory,
		Csv $csvProcessor,
        Context $context
    )
    {
	    $this->_jsonFactory = $jsonFactory;
		
		$this->_datetime = $datetime;
		
		$this->_csvProcessor = $csvProcessor;
		
		$this->productCollectionFactory = $productCollectionFactory;
		
		$this->_partFinderUniversalProductsFactory = $partFinderUniversalProductsFactory;
		
        parent::__construct($context);
    }

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
		$result = ['success' =>false];
		
		 try 
		 {	
			$files = $this->getRequest()->getFiles();
			
			$finder_id = $this->getRequest()->getParam('finder_id');
			
			$delete_existing_data = $this->getRequest()->getParam('delete_existing_data');
			
			if(isset($files['qqfile']['tmp_name']) && $finder_id)
			{
				$universalProductRows = $this->_csvProcessor->getData($files['qqfile']['tmp_name']);				
				
				$skus=[];
				
				foreach($universalProductRows as $row)
				{
					$skus[] = array_merge($skus,$row);
				}
			
				$collection = $this->productCollectionFactory->create();
						
				$collection->addAttributeToSelect('sku');
		
				$collection->addAttributeToFilter('sku',['in'=>$skus]);
				
				foreach($collection as $key=>$product)
				{
					$universal_product_data[$key]['finder_id'] = $finder_id;
					$universal_product_data[$key]['product_id'] = $product->getId();
					$universal_product_data[$key]['sku'] = $product->getSku();
				}				
				
				$uniProductColl = $this->_partFinderUniversalProductsFactory->create()->getCollection();
				$universal_product_table = $uniProductColl->getMainTable(); 
				$connection = $uniProductColl->getConnection(); 		
				
				/*Delete Existing product*/
				if($delete_existing_data)
				{
					$connection->delete($universal_product_table,__("finder_id = %1",$finder_id));
				}
				
				$connection->insertMultiple($universal_product_table,$universal_product_data);
				
				$datetime = $this->_datetime->gmtDate("Y-m-d H:i:s");
				
				$result['success'] = true;
				
				$result['message'] = __("Successfully %1 products imported as universal products.",$universal_product_data);
			}
			else
			{
				$result['message'] = __("Fail to import product.");
			}
        } 
		catch (\Exception $e) 
		{
            $result['message'] = $e->getMessage();
        }

		$jsonResultFactory = $this->_jsonFactory->create();
		
		return $jsonResultFactory->setData($result);
    }
}
