<?php
ini_set('memory_limit', '512M');
ini_set('display_errors', '1');
ini_set('default_charset', 'UTF-8');
require('php-excel-reader/excel_reader2.php');
require('SpreadsheetReader.php');
use Magento\Framework\App\Bootstrap;
include('../app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

$objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true); 

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$Reader = new SpreadsheetReader('catalog_product_20200615_114851.xlsx');
$Sheets = $Reader->Sheets();
$count = 0; 


$storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
$storeIds = array_keys($storeManager->getStores());
$action = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Action');


$table = '<table style="width:100%"><tr><th>ID</th><th>Sku</th><th>Made In</th><th>Store</th><th>compatible_with</th><th>manufacturer</th></tr>';

foreach ($Sheets as $Index => $Name)
{
   $Reader->ChangeSheet($Index);
	
    foreach ($Reader as $keyIndex=>$rowValue)
    {
    	if($keyIndex == 0) continue;

    	//echo '<pre>'; print_r($rowValue[2]);
    	if(!empty($rowValue[0]))
    	{
		  //$table .='<tr><td>'.$rowValue[0].'</td><td>'.$rowValue[1].'</td><td>'.$rowValue[2].'</td></tr>';
            $updateAttributes =[];

            if(isset($rowValue[1]))
            {
                //$updateAttributes['compatible_with'] = $rowValue[1];
            }

            if(isset($rowValue[2]))
            {
                $updateAttributes['made_in'] = $rowValue[2];
                //$updateAttributes['manufacturer'] = $rowValue[2];
            }

            
            $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $collection = $productCollectionFactory->create();
            $collection->addAttributeToFilter('sku', $rowValue[0]);
            $collection->addAttributeToSelect('*');
            foreach ($collection as $product) 
            {
                //$action->updateAttributes([$product->getId()], $updateAttributes, 0); 
                //echo '<pre>'; print_r($product->getData());
               foreach ($storeIds as $storeId) {
                    
                    $action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                    $table .='<tr><td>'.$product->getId().'</td><td>'.$product->getSku().'</td><td>'.$product->getMadeIn().'</td><td>'.$storeId.'</td><td>'.$product->getCompatibleWith().'</td><td>'.$product->getManufacturer().'</td></tr>';
               }
                
            }
    	}
		

	}
}

$table .='</table>';

echo $table;


?>
