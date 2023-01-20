<?php
namespace Mageants\BaseProduct\Model\Import;

use Mageants\BaseProduct\Model\Import\ProductImport\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use \Mageants\Product360Image\Helper\Data;
use \Magento\Framework\Filesystem;

class ProductImport extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const PRODUCT_ID = 'product_id';
    const TABLE_ENTITY = 'mageants_product360image';
    const SETTING = 'setting';
    const IMAGES = 'images';
    const SKU = 'sku';
    public $messageTemplates = [
        ValidatorInterface::ERROR_INVALID_IMAGE => 'Only jpg and png files allowed'
    ];
    
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
   
    public $permanentAttributes = [self::PRODUCT_ID];
    /**
     * If we should check column names
     *
     * @var bool
     */
    public $needColumnCheck = true;
    /**
     * Banner Data Helper
     *
     * @var \Magento\Backend\Model\Session
     */
    public $bannerHelper;
    /**
     * Valid column names
     *
     * @array
     */
    public $validColumnNames = [
        self::PRODUCT_ID,
        self::SKU,
        self::SETTING,
        self::IMAGES,
    ];
    public $specialAttributes = [self::PRODUCT_ID];
    /**
     * Need to log in import history
     *
     * @var bool
     */
    public $logInHistory = true;
    public $validators = [];
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $connection;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    public $resource;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    public $subDir = 'mageants/product360image/product360';
    /**
     * File system model
     *
     * @var \Magento\Framework\Filesystem
     */
    public $fileSystem;
    public $context;
    /**
     * @var \Magento\ImportExport\Model\Import\Config
     */
    public $importConfig;
    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $fileSystem,
        Data $bannerHelper,
        \Magento\ImportExport\Model\Import\Config $importConfig
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->importConfig = $importConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->fileSystem = $fileSystem;
        $this->bannerHelper = $bannerHelper;
        $this->storeManager = $storeManager;
        $this->_initTypeModels();
    }
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }
    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'product_import';
    }
    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $count = 1;
        $data_images = explode(",", $rowData[self::IMAGES]);
        foreach ($data_images as $image) {
            $data = explode("|", $image);
            if (isset($data[1])) {
                $type=[1 => 'jpg',2 => 'png'];
                $ext = explode(".", $data[1]);
                if (!in_array($ext[1], $type)) {
                    $this->addRowError(ValidatorInterface::ERROR_INVALID_IMAGE, $rowNum);
                }
            }
           
            $count++;
        }
       
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }
    public function _initTypeModels()
    {
        
        $this->_initErrorTemplates();
        return $this;
    }
    public function _initErrorTemplates()
    {
        foreach ($this->messageTemplates as $errorCode => $template) {
            $this->addMessageTemplate($errorCode, $template);
        }
    }
    /**
     * Create Advanced message data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    public function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }
        return true;
    }
    /**
     * Save Message
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    public function getConvertedId($product_id = null)
    {
        if ($product_id) {
            return base64_encode($product_id);
        } else {
            return '';
        }
    }
    public function getProduct360Path($product_id)
    {
        return $this->subDir.'/image/'.$this->getConvertedId($product_id) . '/';
    }
    /**
     * Save and replace data message
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList_csv = [];
            foreach ($bunch as $rowNum => $rowData) {
                $product360setting = [];
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_TITLE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowTtile= $rowData[self::PRODUCT_ID];

                $listTitle[] = $rowTtile;
                $data = explode("|", $rowData[self::SETTING]);
                $product360setting['setting']['product_id'] = $rowData[self::PRODUCT_ID];
                if ($data[0] != null) {
                    $product360setting['setting']['height'] =  $data[0];
                } else {
                    $product360setting['setting']['height'] = '400';
                }
                if ($data[1] != null) {
                    $product360setting['setting']['width'] =  $data[1];
                } else {
                    $product360setting['setting']['width'] = '600';
                }
                if ($data[2] != null) {
                    $product360setting['setting']['frameTime'] =  $data[2];
                } else {
                    $product360setting['setting']['frameTime'] = '300';
                }
                if ($data[3] != null) {
                    $product360setting['setting']['enable_fullscreen'] =  $data[3];
                } else {
                    $product360setting['setting']['enable_fullscreen'] = 1;
                }
                if ($data[4] != null) {
                    $product360setting['setting']['enable_ease_blur'] =  $data[4];
                } else {
                    $product360setting['setting']['enable_ease_blur'] = 1;
                }
                if ($data[5] != null) {
                    $product360setting['setting']['images_mode'] =  $data[5];
                } else {
                    $product360setting['setting']['images_mode'] = 0;
                }
                if ($data[6] != null) {
                    if ($product360setting['setting']['images_mode'] == 1) {
                        $product360setting['setting']['frames'] = $data[6];
                    } else {
                        $product360setting['setting']['frames'] = null;
                    }
                } else {
                    if ($product360setting['setting']['images_mode'] == 1) {
                        $product360setting['setting']['frames'] = '1';
                    } else {
                        $product360setting['setting']['frames'] = null;
                    }
                }
                if ($data[7] != null) {
                    if ($product360setting['setting']['images_mode'] == 1) {
                        $product360setting['setting']['frames'] = $data[7];
                    } else {
                        $product360setting['setting']['frames'] = null;
                    }
                } else {
                    if ($product360setting['setting']['images_mode'] == 1) {
                        $product360setting['setting']['framesX'] = '1';
                    } else {
                        $product360setting['setting']['framesX'] = null;
                    }
                }

                $count = 1;
                $all_image_data = explode(",", $rowData[self::IMAGES]);
                $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                $image_path = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath().$this->getProduct360Path($product360setting['setting']['product_id']);
                $image_source = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath().'import';
                $product360images = [];
                foreach ($all_image_data as $allImages) {
                    $image_data = explode("|", $allImages);
                    if (isset($image_data[1])) {
                        $image_url = $baseUrl.'pub/media/'.$this->getProduct360Path($product360setting['setting']['product_id']).basename($image_data[1]);
                        $org_image = $image_source.'/'.$image_data[1];
                        $destination= $image_path;
                        $img_name=basename($org_image);
                        $new_name = basename($image_path);
                        if (!file_exists($destination)) {
                            mkdir($destination, 0777, true);
                        }
                        if (rename($org_image, $destination.$img_name)) {
                            $product360images['images']['image'.$count]['position'] = $image_data[0];
                            $product360images['images']['image'.$count]['file'] = basename($image_data[1]);
                            $product360images['images']['image'.$count]['url'] = $image_url;
                            $product360images['images']['image'.$count]['path'] = $image_path;
                            $product360images['images']['image'.$count]['value_id'] = '';
                            $product360images['images']['image'.$count]['label'] = '';
                            $product360images['images']['image'.$count]['disabled'] = '0';
                            $product360images['images']['image'.$count]['media_type'] = 'image';
                            $product360images['images']['image'.$count]['removed'] = '';
                        }
                    }
                   
                    $count++;
                }
                if (isset($product360setting) && isset($product360images)) {
                    $entityList_data= $this->bannerHelper->serializeSetting($product360setting);
                    $entityList_image_data= $this->bannerHelper->serializeSetting($product360images);
                    $entityList_csv[$rowTtile][] = [
                        self::PRODUCT_ID => $rowData[self::PRODUCT_ID],
                        self::SKU => $rowData[self::SKU],
                        self::SETTING => $entityList_data,
                        self::IMAGES => $entityList_image_data,
                    ];
                }
            }
            //if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                $this->saveEntityFinish($entityList_csv, self::TABLE_ENTITY);
                if ($listTitle) {
                    if ($this->deleteEntityFinish(array_unique($listTitle), self::TABLE_ENTITY)) {
                        $this->saveEntityFinish($entityList_csv, self::TABLE_ENTITY);
                    }
                }
            /*} elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList_csv, self::TABLE_ENTITY);
            }*/
        }
        return $this;
    }
    /**
     * Save message to customtable.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    public function saveEntityFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {
                $this->connection->insertOnDuplicate($tableName, $entityIn, [
                    self::PRODUCT_ID,
                    self::SKU,
                    self::SETTING,
                    self::IMAGES
                ]);
            }
        }
        return $this;
    }
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    public function deleteEntity()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::PRODUCT_ID];
                    $listTitle[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
            if ($listTitle) {
                $this->deleteEntityFinish(array_unique($listTitle), self::TABLE_ENTITY);
            }
            return $this;
        }
    }
    public function deleteEntityFinish(array $listTitle, $table)
    {
        if ($table && $listTitle) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName($table),
                    $this->connection->quoteInto('product_id IN (?)', $listTitle)
                );
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
