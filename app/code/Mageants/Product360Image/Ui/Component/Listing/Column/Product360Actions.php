<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author    Mageants Team <info@mageants.com>
  */
namespace Mageants\Product360Image\Ui\Component\Listing\Column;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Mageants\Product360Image\Model\Product360Factory;
        
class Product360Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_NEW = 'mageants_product360image/product360/new';
    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'mageants_product360image/product360/edit';

    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'mageants_product360image/product360/delete';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * URL builder
     *
     * @var \Mageants\Product360Image\Model\Product360Factory
     */
    public $product360Factory;

    /**
     * constructor
     *
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Product360Factory $product360Factory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        Product360Factory $product360Factory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        
        $this->product360Factory = $product360Factory;
        
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $prod_ids=[];
            
            foreach ($dataSource['data']['items'] as & $item) {
                $prod_ids[] = $item['entity_id'];
            }
            
            $product360Factory = $this->product360Factory->create();
            
            $peoduct360Collection = $product360Factory->getCollection();
            
            $peoduct360Collection->addFieldToSelect(['product_id','id'])
                    ->addFieldToFilter('product_id', ['in' => $prod_ids]);
            
            $product360_product_Ids = [];
            
            foreach ($peoduct360Collection as & $product360) {
                $product360_product_Ids[] = $product360->getProductId();
                $product360_Ids[$product360->getProductId()] = $product360->getId();
            }
            
            foreach ($dataSource['data']['items'] as & $item) {
                $storeId = $this->context->getFilterParam('store_id');
              
                $item[$this->getData('name')]['edit'] = [
                                'href' => $this->urlBuilder->getUrl(
                                    'catalog/product/edit',
                                    ['id' => $item['entity_id'], 'store' => $storeId]
                                ),
                                'label' => __('Edit'),
                                'hidden' => false,
                    ];
                
                if (in_array($item['entity_id'], $product360_product_Ids)) {
                    $item[$this->getData('name')]['product360edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $product360_Ids[$item['entity_id']],
                                    'productid' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Product360 Edit')
                        ];
                        
                    $item[$this->getData('name')]['product360delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $product360_Ids[$item['entity_id']],
                                    'productid' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Product360 Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.name }"'),
                                'message' => __('Are you sure you wan\'t to delete the 
                                    Product360 "${ $.$data.name }" ?')
                            ]
                    ];
                } else {
                    $item[$this->getData('name')]['product360new'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_NEW,
                                [
                                    'productid' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Product360 New')
                        ];
                }
            }
        }
        
        return $dataSource;
    }
}
