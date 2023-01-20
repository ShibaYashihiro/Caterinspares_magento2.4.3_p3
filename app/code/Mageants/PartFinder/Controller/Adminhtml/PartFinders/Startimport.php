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
use \Mageants\PartFinder\Model\PartFindersFactory;
use \Mageants\PartFinder\Model\PartFinderImportFilesFactory;
use \Mageants\PartFinder\Model\PartFinderImportFilesHistoryFactory;
use \Mageants\PartFinder\Model\PartFinderImportFilesLogsFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;	
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory as PartFinderOptionValuesFactory;	
use \Mageants\PartFinder\Model\PartFinderOptionValueMapFactory as PartFinderOptionValueMapFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;
use \Mageants\PartFinder\Model\Upload;
use Mageants\PartFinder\Model\ResourceModel\Files;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\File\Csv;
use \Mageants\PartFinder\Helper\Data as FinderHelper; 
use \Magento\Catalog\Api\ProductRepositoryInterface;

class Startimport extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinder_csv_start_import';
	  /**
     * PartFindersFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFindersFactory
     */
    protected $_partFinders;  
	/**
     * CollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionsCollection;
	/**
     * PartFinderImportFilesHistoryFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesHistoryFactory
     */
    protected $_partFinderImportFilesHistoryFactory;
	/**
     * PartFinderImportFilesLogsFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesLogsFactory
     */
    protected $_partFinderImportFilesLogsFactory;
	/**
     *  PartFinderOptionValuesCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory
     */
    protected $_partFinderOptionValuesCollectionFactory;
	/**
     * PartFinderOptionValueMapCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory
     */
    protected $_partFinderOptionValueMapCollectionFactory;
	/**
     *  PartFinderOptionValues model
     * 
     * @var use \Mageants\PartFinder\Model\PartFinderOptionValues;	
     */
    protected $_partFinderOptionValuesFactory;
	/**
     * PartFinderOptionValueMap model
     * 
     * @var use \Mageants\PartFinder\Model\PartFinderOptionValueMap
     */
    protected $_partFinderOptionValueMapFactory;
	/**
     * ProductRepositoryInterface model
     * 
     * @var use \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;
	/**
     * PartFinderImportFilesFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory
     */
    protected $_partFinderImportFiles;
	/**
     * @var \Mageants\PartFinder\Model\ResourceModel\Files
     */
    protected $_filesModel;
    /**
     * DateTime
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    /**
     * @var \Mageants\PartFinder\\Helper\Data;
     */
    protected $_finderHelper;
    /**
     * Csv
     * 
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvProcessor;
    /**
     * constructor
     * 
     * @param JsonFactory $jsonFactory
     * @param PartFinderImportFilesFactory $PartFinderImportFiles
     * @param PartFindersFactory $partFinders
     * @param PartFinderOptionsFactory $partFinderOptionFactory
     * @param Files $filesModel
     * @param DateTime $datetime
     * @param Csv $csvProcessor
     * @param FinderHelper $helper
     * @param Context $context
     */
    public function __construct(
		JsonFactory $jsonFactory,
		PartFinderImportFilesFactory $partFinderImportFiles,
		PartFindersFactory $partFinders,
		PartFinderOptionsCollectionFactory $partFinderOptionCollectionFactory,		
		PartFinderImportFilesHistoryFactory $partFinderImportFilesHistoryFactory,
		PartFinderImportFilesLogsFactory $partFinderImportFilesLogsFactory,
		PartFinderOptionValuesCollectionFactory $partFinderOptionValueCollectionFactory,
		PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory,
		PartFinderOptionValuesFactory $partFinderOptionValueFactory,
		PartFinderOptionValueMapFactory $partFinderOptionValueMapFactory,
		ProductRepositoryInterface $productRepositoryInterface,
		FinderHelper $finderHelper,
		Files $filesModel,
		DateTime $datetime,
		Csv $csvProcessor,
        Context $context
    )
    {
	    $this->_jsonFactory = $jsonFactory;
		
		$this->_finderHelper = $finderHelper;
		
		$this->_filesModel = $filesModel;
		
		$this->_datetime = $datetime;
		
		$this->_partFinderImportFiles = $partFinderImportFiles;
		
		$this->_partFinders = $partFinders;
		
		$this->_partFinderOptionsCollection = $partFinderOptionCollectionFactory;
		
		$this->_partFinderImportFilesHistoryFactory = $partFinderImportFilesHistoryFactory;
		
		$this->_partFinderImportFilesLogsFactory = $partFinderImportFilesLogsFactory;
		
		$this->_partFinderOptionValuesCollectionFactory = $partFinderOptionValueCollectionFactory;
		
		$this->_partFinderOptionValueMapCollectionFactory = $partFinderOptionValueMapCollectionFactory;
		
		$this->_partFinderOptionValuesFactory = $partFinderOptionValueFactory;
		
		$this->_partFinderOptionValueMapFactory = $partFinderOptionValueMapFactory;
		
		$this->_productRepositoryInterface = $productRepositoryInterface;
		
		$this->_csvProcessor = $csvProcessor;
		
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
		$result['success'] = false;
					
        $id = $this->getRequest()->getParam('id');
		
        $finder_id = $this->getRequest()->getParam('finder_id');
		
        $delete_existing_data = $this->getRequest()->getParam('delete_existing_data');
		
		$log_data = [];
		 try {			
			$data = array();
			
			/** @var \Mageants\PartFinder\Model\PartFinderImportFilesFactory $partfinder_file */
			$partfinder_file = $this->_partFinderImportFiles->create();
			
			$partfinder_file->load($id);
			
			$base_path = $this->_filesModel->getBaseDir($finder_id);
				
			$filepath = $base_path . DIRECTORY_SEPARATOR . $partfinder_file->getFileName();
			
			if (!isset($filepath) ||  !file_exists($filepath)) 
			{
				 $result['message'] = __('Invalid file to import or file does not exist.');
			 }
			 else
			 { 
				$last_stop_index = $partfinder_file->getProcessedRows();
				
				$start_from_begining = $this->getRequest()->getParam("start_from_begining");
				
				if($start_from_begining)
				{
					$last_stop_index = 0;
				}
				
				$processed_rows =  $last_stop_index;
				
				$processrow = 0;
				
				$count_errors = 0;
				
				$max_row_to_import = $this->_finderHelper->getMaxRowImport();
				
				$startdatetime = $updatedatetime = $this->_datetime->gmtDate("Y-m-d H:i:s");
				
				$totalrow = $partfinder_file->getTotalRow();
				
				$partFinderImportFilesHistoryFactory = $this->_partFinderImportFilesHistoryFactory->create();
				
				$partFinderImportFilesHistoryFactory->setFinderId($finder_id)
					->setImportFileId($id)
					->setFileName($partfinder_file->getFileName())
					->setImportStartAt($startdatetime)
					->setTotalRow($totalrow)
					->save();
				
				$partFinderOptionsCollection = $this->_partFinderOptionsCollection->create();
				
				$partFinderOptionsCollection->addFieldToFilter("finder_id",$partfinder_file->getFinderId());
				
				$finder_options = [];
				
				foreach($partFinderOptionsCollection as $fnd_opt)
				{
					$finder_options[$fnd_opt->getId()] = $fnd_opt->getData();
				}
				
				$history_id = $partFinderImportFilesHistoryFactory->getId();
					
				$importProductRawData = $this->_csvProcessor->getData($filepath);
				
				 /*Check import data exist*/
				if(count($importProductRawData) > 1)
				{
					/*Match Option count with csv column*/
					if(count($finder_options) == (count($importProductRawData[0])-1))
					{
						/*Check sku coumn exist */
						if((count($importProductRawData[0])) == (count($finder_options)+1))
						{	
							/*Delete existing all old data and import new data*/
							if($delete_existing_data)
							{
								$options_ids = [];
								
								foreach($finder_options as $opt_id => $fnd_opt)
								{
									$options_ids[] = $opt_id;
								}
								
								$options_ids = implode(",",$options_ids);
								
								$partFinderOptionValueMapFactory = $this->_partFinderOptionValueMapFactory->create()->getCollection();
								$value_map_table = $partFinderOptionValueMapFactory->getMainTable(); 
								
								$partFinderOptionValueCollection = $this->_partFinderOptionValuesCollectionFactory->create();								
								$value_table = $partFinderOptionValueCollection->getMainTable();
								
								$connection = $uniProductColl->getConnection();
								$sub_delete_query = "SELECT id FROM {$value_table} WHERE option_id IN ({$options_ids})";
								$connection->delete($value_map_table,__("value_id in (%1)",$sub_delete_query));
							}
							
							$option_value_map = $this->getMapedOptionsValue($finder_options);
							
							/*Import Process*/
							foreach ($importProductRawData as $rowIndex => $dataRow) 
							{
								/*skip first label row*/
								if($rowIndex == 0) continue;
								
								/* break when rich max row import in each import*/
								if($processrow >= $max_row_to_import || $processed_rows >= $totalrow) break;
								
								/*skip all record untile rich to last executed row in last import*/
								if(!$start_from_begining && $rowIndex <= $last_stop_index) continue;
									
								$processed_rows++;
							
								/*Check sku column data is not blank*/
								if($dataRow[ count($dataRow) -1 ]  == "") 
								{
									$log_data[] = [
											'import_file_id'=> $partfinder_file->getId(),
											'finder_import_history_id'=> $history_id,
											'row_number'=> $rowIndex ,
											'message'=> "Sku column find empty", 
											'import_start_at'=> $startdatetime, 
											'create_at'=> $this->_datetime->gmtDate("Y-m-d H:i:s") 
										];
									$count_errors++;
									$processrow++;
									continue;
								}
								
								/*check current row column count*/
								if((count($dataRow)) != (count($finder_options)+1))
								{
										$log_data[] = [
											'import_file_id'=> $partfinder_file->getId(),
											'finder_import_history_id'=> $history_id,
											'row_number'=> $rowIndex ,
											'message'=> "Column count mis match", 
											'import_start_at'=> $startdatetime, 
											'create_at'=> $this->_datetime->gmtDate("Y-m-d H:i:s") 
										];
									$count_errors++;
									$processrow++;
									continue;
								}
								
								/*Check option column data is not blank*/
								$option_value_error = false;
								
								foreach($dataRow as $column=>$value)
								{
									if($value == "")
									{
										$log_data[] = [
												'import_file_id'=> $partfinder_file->getId(),
												'finder_import_history_id'=> $history_id,
												'row_number'=> $rowIndex, 
												'message'=> "Option(column) ".($column + 1)." find empty", 
												'import_start_at'=> $startdatetime, 
												'create_at'=> $this->_datetime->gmtDate("Y-m-d H:i:s") 
											];
										$option_value_error = true;
									}
								}
								
								if($option_value_error)
								{
									$count_errors++;
									$processrow++;
									continue;
								}
								
								/*Start importing*/
								$tree_lavel = 0 ; 
								
								$parent_id = 0;
								
								$parent_val = "";
								
								$column_index = 0;
								
								$sku = $dataRow[count($dataRow)-1];
								
								$product = $this->_productRepositoryInterface->get($sku);
								
								/*Check Product Exist */
								if($product->getId())
								{	
									$tmp_key = "";
									
									$is_row_updated = false;
									
									foreach($finder_options as $opt_id=>$fnd_option)
									{
										$col_value = $dataRow[$column_index];
										
										$tmp_key .= $parent_val . "-" . $col_value . "-" . $tree_lavel;
										
										if(isset($option_value_map[$tmp_key]))
										{
											$parent_id = $option_value_map[$tmp_key]['id'];
										}
										else
										{
											$is_row_updated = true;
											
											$partFinderOptionValue = $this->_partFinderOptionValuesFactory->create();
												
											$data= [
												'parent_id'=>$parent_id,
												'option_id'=>$opt_id,
												'value'=>$col_value
											];
											
											$partFinderOptionValue->setData($data)->save();
											
											/*Update option_value_map array with new insert data*/
											$option_value_map[ $tmp_key ]['id']= $partFinderOptionValue->getId();
											$option_value_map[ $tmp_key ]['value']= $col_value;
											$option_value_map[ $tmp_key ]['parent_id']= $parent_id;
											$option_value_map[ $tmp_key ]['parent_val']= $parent_val;
											$option_value_map[ $tmp_key ]['opt_id']= $opt_id;
											
											$parent_id = $partFinderOptionValue->getId();											
										}
										
										$tree_lavel++;
										
										$parent_val = $col_value;
										
										$column_index++;
									}
									
									$tmp_key = $parent_id;
									
									if( !isset( $option_value_map[ $tmp_key ] ) || !in_array($sku,$option_value_map[ $tmp_key ]['sku']) )
									{
										$partFinderOptionValueMap = $this->_partFinderOptionValueMapFactory->create();
										
										$data = [
											'value_id'=>$parent_id,
											'product_id'=>$product->getId(),
											'sku'=>$sku
										];
										
										$partFinderOptionValueMap->setData($data)->save();
										
										/*Update option_value_map array with new insert data*/
										$option_value_map[ $tmp_key ]['sku'][]= $sku;
										$option_value_map[ $tmp_key ]['id']= $partFinderOptionValueMap->getId();
										$option_value_map[ $tmp_key ]['parent_id'] = $parent_id;
										$option_value_map[ $tmp_key ]['parent_val'] = $parent_val;
									}
									else if(!$is_row_updated)
									{
										$log_data[] = [
											'import_file_id'=> $partfinder_file->getId(),
											'finder_import_history_id'=> $history_id,
											'row_number'=> $rowIndex, 
											'message'=> "Same Row already exist : ".json_encode($dataRow), 
											'import_start_at'=> $startdatetime, 
											'create_at'=> $this->_datetime->gmtDate("Y-m-d H:i:s") 
										];
										$count_errors++;
									}
									
									$processrow++;		
								}
								else
								{
									$log_data[] = [
										'import_file_id'=> $partfinder_file->getId(),
										'finder_import_history_id'=> $history_id,
										'row_number'=> $rowIndex, 
										'message'=> "Product not exist of sku : {$sku}", 
										'import_start_at'=> $startdatetime, 
										'create_at'=> $this->_datetime->gmtDate("Y-m-d H:i:s") 
									];
									$count_errors++;
									$processrow++;		
								}
							}
							
							$importFileLogColl = $this->_partFinderImportFilesLogsFactory->create()->getCollection();
							$log_table = $importFileLogColl->getMainTable(); 
							$log_connection = $importFileLogColl->getConnection(); 							
							$log_connection->insertMultiple($log_table,$log_data);
							
							$enddatetime = $this->_datetime->gmtDate("Y-m-d H:i:s");
							
							
							if($start_from_begining)
							{
								$data = [
									'id' => $partfinder_file->getId(),
									'processed_rows' => $processed_rows,
									'status' => 1,
									'import_start_at' => $startdatetime,
									'import_update_at' => $startdatetime,
									'import_ended_at' => $startdatetime
								];
							}
							else
							{
								$data = [
									'id' => $partfinder_file->getId(),
									'processed_rows' => $processed_rows,
									'status' => 1,
									'import_update_at' => $updatedatetime,
									'import_ended_at' => $enddatetime
								];
							}
							
							$partfinder_file->setData($data)->save();
							
							$partFinderImportFilesHistoryFactory->setFinderId($finder_id)
								->setCountErrors($count_errors)
								->setImportEndedAt($startdatetime)
								->setProcessedRows($processrow)
								->save();
								
							$result['success'] = true;
							$result['message'] = __("Successfully imported : %1 number of row processed with %1 error out of %1. ",$processrow,$count_errors,$totalrow);
						}
						else
						{
							$result['message'] = __("Error! value column not exist.");
						}
					}
					else
					{
						$result['message'] = __("Error! option column count not match.");
					}
				}
				else
				{
					$result['message'] = __("Error! no data rows found.");
				}
			 }
        } 
		catch (\Exception $e) 
		{
            $result['message'] = $e->getMessage();
        }

		$jsonResultFactory = $this->_jsonFactory->create();
		
		return $jsonResultFactory->setData($result);
    }
	
	protected function getMapedOptionsValue($finder_options = [])
	{
		$opt_vals = [];
		$data = [];
		$data_serialize = [];
		
		if(count( $finder_options ))
		{
			$partFinderOptionValueMapCollection = $this->_partFinderOptionValueMapCollectionFactory->create();
								
			$value_map_table = $partFinderOptionValueMapCollection->getMainTable();
			
			$partFinderOptionValueCollection = $this->_partFinderOptionValuesCollectionFactory->create();
			
			$value_table = $partFinderOptionValueCollection->getMainTable();
			
			$last_table_field = 'main_table.value_id' ;
			
			foreach(array_reverse(array_keys($finder_options)) as $opt_id)
			{
				 $partFinderOptionValueMapCollection  
				 ->getSelect()
				 ->joinLeft(
				   ['value_'.$opt_id => $value_table],
				   $last_table_field.' = value_'. $opt_id .'.id',
				   ['value'.$opt_id=>'value_'. $opt_id .'.value','id'.$opt_id=>'value_'. $opt_id .'.id']
				   );				   
				   $last_table_field = 'value_'.$opt_id.".parent_id";
				   
				  $partFinderOptionValueMapCollection->addFieldToFilter("value_{$opt_id}.option_id",['in'=>array_keys($finder_options)]);
			}	
			//echo $partFinderOptionValueMapCollection->getSelect();exit;
					
			$option_column_index = 0;
			
			foreach($partFinderOptionValueMapCollection as $key => $value_map_data)
			{	
				$tree_lavel = 0 ; 
				$parent_id = 0;
				$parent_val = "";
				$tmp_key = '';
				
				foreach($finder_options as $opt_id=>$option_data)		
				{			
					$tmp_key .= $parent_val . "-". $value_map_data->getData("value".$opt_id) . "-" . $tree_lavel; 
					
					$opt_vals[ $tmp_key ]['id']= $value_map_data->getData("id".$opt_id);
					$opt_vals[ $tmp_key ]['value']= $value_map_data->getData("value".$opt_id);
					$opt_vals[ $tmp_key ]['parent_id']= $parent_id;
					$opt_vals[ $tmp_key ]['parent_val']= $parent_val;
					$opt_vals[ $tmp_key ]['opt_id']= $opt_id;
					
					$parent_id= $value_map_data->getData("id".$opt_id);
					$parent_val= $value_map_data->getData("value".$opt_id);

					$tree_lavel++;
				}
				
				$tmp_key = $parent_id; 
				
				/*Sku and parent last option*/
				$opt_vals[ $tmp_key ]['sku'][] = $value_map_data->getData("sku");
				$opt_vals[ $tmp_key ]['id']= $value_map_data->getData("id");
				$opt_vals[ $tmp_key ]['parent_id'] = $value_map_data->getData("value_id");
				$opt_vals[ $tmp_key ]['parent_val'] = $parent_val;				
			}
		}
		/* 
 		 echo "<pre>";
		print_r($opt_vals);
		exit;      */
		return $opt_vals;
	}
}
