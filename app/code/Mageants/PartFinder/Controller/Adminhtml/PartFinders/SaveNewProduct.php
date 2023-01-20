<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\PartFindersFactory;
use \Mageants\PartFinder\Model\PartFinderOptionsFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory as PartFinderOptionValuesFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValueMapFactory as PartFinderOptionValueMapFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Catalog\Api\ProductRepositoryInterface;

class SaveNewProduct extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_PartFinder::partfinders_save';
    /**
     * PartFinder Data Helper
     *
     * @var \Mageants\PartFinder\Model\PartFinderOptionsFactory
     */
    protected $_partFinderOptionsFactory;
    /**
     * ProductRepositoryInterface model
     *
     * @var use \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;
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
     * constructor
     *
     * @param Upload $uploadModel
     * @param File $fileModel
     * @param Image $imageModel
     * @param Session $backendSession
     * @param PartFindersFactory $partfindersFactory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        PartFindersFactory $partfindersFactory,
        PartFinderOptionsFactory $partFinderOptionFactory,
        PartFinderOptionValuesFactory $partFinderOptionValueFactory,
        PartFinderOptionValueMapFactory $partFinderOptionValueMapFactory,
        Registry $registry,
        Context $context,
        ProductRepositoryInterface $productRepositoryInterface
    ) {
        $this->_partFinderOptionsFactory = $partFinderOptionFactory;
        
        $this->_productRepositoryInterface = $productRepositoryInterface;
        
        $this->_partFinderOptionValuesFactory = $partFinderOptionValueFactory;
        
        $this->_partFinderOptionValueMapFactory = $partFinderOptionValueMapFactory;
            
        parent::__construct($partfindersFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $partfinder = $this->_initPartFinder();
        
        $finder_id = $partfinder->getId();
        
        $data = $this->getRequest()->getPost('newproduct');
        
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $resultRedirect->setPath(
            '*/*/addNewRow',
            [
                'id' => $finder_id,
                '_current' => true
            ]
        );
        if ($data) {
            try {
                $sku = $data['sku'];
                
                $product = $this->_productRepositoryInterface->get($sku);
                
                if (isset($data['option']) && is_array($data['option'])) {
                    $partFinderOptionValuesFactory = $this->_partFinderOptionValuesFactory->create();
                    
                    $parent = 0;
                    $count = 0;
                    $first_option = null;

                    foreach ($data['option'] as $opt_id => $opt_val) {

                        if($count == 0){
                            $first_option = $this->_partFinderOptionValuesFactory->create()->getCollection()
                                    ->addFieldToFilter("value", $opt_val)
                                    ->addFieldToFilter("option_id", $opt_id)
                                    ->addFieldToFilter("parent_id", $parent)
                                    ->getFirstItem();
                        }

                        $option_val = $partFinderOptionValuesFactory->getCollection()
                            ->addFieldToFilter("value", $opt_val)
                            ->addFieldToFilter("option_id", $opt_id)
                            ->addFieldToFilter("parent_id", $parent)
                            ->getFirstItem();

                        if($first_option && !empty($first_option->getData())){
                            $option_val_Check = $partFinderOptionValuesFactory->getCollection()
                                ->addFieldToFilter("value", $opt_val)
                                ->addFieldToFilter("option_id", $opt_id)
                                ->addFieldToFilter("parent_id", $parent)
                                ->getFirstItem();
                            if (!empty($option_val_Check->getData())) {
                                $parent = $option_val_Check->getData('id');
                            }
                            if (empty($option_val_Check->getData())) {
                                $data_val = [
                                    'value'=>$opt_val,
                                    'option_id'=>$opt_id,
                                    'parent_id'=>$parent
                                ];

                                $option_val->setData($data_val)->save();

                                $parent = $option_val->getId();
                            }
                        }else{
                            $data_val = [
                                'value'=>$opt_val,
                                'option_id'=>$opt_id,
                                'parent_id'=>$parent
                            ];

                            $option_val->setData($data_val)->save();

                            $parent = $option_val->getId();
                        }
                            
                        $count++;
                    }

                    $existingData = $this->_partFinderOptionValueMapFactory->create()
                        ->getCollection()->addFieldToFilter('value_id',$parent)
                        ->addFieldToFilter('sku',$sku)
                        ->addFieldToFilter('product_id',$product->getId())
                        ->getData();

                    if(count($existingData) == 0){
                        $partFinderOptionValueMapFactory = $this->_partFinderOptionValueMapFactory->create();
                        $data_val = [
                                'value_id'=>$parent,
                                'sku'=>$sku,
                                'product_id'=>$product->getId()
                            ];
                            
                        $partFinderOptionValueMapFactory->setData($data_val)->save();
                        
                        $this->messageManager->addSuccess(__('The Part Finder New Product Added.'));

                        $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'id' => $finder_id,
                            ]
                        );
                    }else{
                        $this->messageManager->addError(__('This product is already existed.'));
                    }
                } else {
                    $this->messageManager->addError(__("Options data found blank please try again."));
                }
                
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, $e->getMessage());
            }
                
            return $resultRedirect;
        }
        
        $resultRedirect->setPath('*/*/edit/id/'.$finder_id);
        
        return $resultRedirect;
    }
    
    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Self::RESOURCE_ID);
    }
}
