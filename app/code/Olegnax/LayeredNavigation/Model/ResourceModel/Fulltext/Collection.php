<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\ResourceModel\Fulltext;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\LayeredNavigation\Model\ResourceModel\Search\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zend_Db_Exception;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    /**
     * @var null
     */
    public $collectionClone = null;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var ProductMetadataInterface
     */
    protected $_productMetadata;

    protected $_categoryId;
    protected $_isTwoOne;
    private $queryText;
    private $order = [];
    private $searchRequestName;
    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;
    /**
     * @var SearchInterface
     */
    private $search;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var SearchResultInterface
     */
    private $searchResult;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param ProductMetadataInterface $productMetadata
     * @param AdapterInterface|null $connection
     * @param string $searchRequestName
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        /** @noinspection PhpUndefinedClassInspection */ LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        TemporaryStorageFactory $temporaryStorageFactory,
        ProductMetadataInterface $productMetadata,
        AdapterInterface $connection = null,
        $searchRequestName = 'catalog_view_container'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchRequestName = $searchRequestName;
        $this->_scopeConfig = $scopeConfig;
        $this->_productMetadata = $productMetadata;
        $this->_isMTwoOneVersion();
    }

    /**
     * @return bool
     */
    protected function _isMTwoOneVersion()
    {
        $version = $this->_productMetadata->getVersion();
        $pos = strpos($version, '2.1');

        if ($pos === false) {
            $this->_isTwoOne = false;
        } else {
            $this->_isTwoOne = true;
        }

        return $this->_isTwoOne;
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function addLayerCategoryFilter($categories)
    {
        $this->addFieldToFilter('category_ids', implode(',', $categories));

        return $this;
    }

    /**
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->searchResult !== null) {
            throw new RuntimeException('Illegal state');
        }

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();

        if (is_array($condition) && $this->_isElasticSearchEngine()) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } elseif (!is_array($condition) || !in_array(key($condition), ['from', 'to'])) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } else {
            if (!empty($condition['from'])) {
                $this->filterBuilder->setField("{$field}.from");
                $this->filterBuilder->setValue($condition['from']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
            if (!empty($condition['to'])) {
                $this->filterBuilder->setField("{$field}.to");
                $this->filterBuilder->setValue($condition['to']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
        }

        return $this;
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
        }

        return $this->searchCriteriaBuilder;
    }

    /**
     * @param SearchCriteriaBuilder $object
     */
    public function setSearchCriteriaBuilder(SearchCriteriaBuilder $object)
    {
        $this->searchCriteriaBuilder = $object;
    }

    /**
     * @return FilterBuilder
     */
    private function getFilterBuilder()
    {
        if ($this->filterBuilder === null) {
            $this->filterBuilder = ObjectManager::getInstance()->get(FilterBuilder::class);
        }

        return $this->filterBuilder;
    }

    /**
     * @return bool
     */
    protected function _isElasticSearchEngine()
    {
        $currentEngine = $this->_scopeConfig->getValue(
            EngineInterface::CONFIG_ENGINE_PATH,
            ScopeInterface::SCOPE_STORE
        );
        if ($currentEngine == 'elasticsearch' || $currentEngine == 'elasticsearch5') {
            return true;
        }

        return false;
    }

    /**
     * @param $attributeCode
     * @return Collection
     */
    public function removeAttributeSearch($attributeCode)
    {
        if (is_array($attributeCode)) {
            foreach ($attributeCode as $attCode) {
                $this->searchCriteriaBuilder->removeFilter($attCode);
            }
        } else {
            $this->searchCriteriaBuilder->removeFilter($attributeCode);
        }

        $this->_isFiltersRendered = false;

        return $this->loadWithFilter();
    }

    /**
     * @param $attribute
     * @param $condition
     * @param string $joinType
     * @return string
     */
    public function getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        return $this->_getAttributeConditionSql($attribute, $condition, $joinType);
    }

    /**
     * @return $this
     */
    public function resetTotalRecords()
    {
        $this->_totalRecords = null;

        return $this;
    }

    /**
     * @param SearchInterface $object
     */
    public function setSearch(SearchInterface $object)
    {
        $this->search = $object;
    }

    /**
     * @param FilterBuilder $object
     */
    public function setFilterBuilder(FilterBuilder $object)
    {
        $this->filterBuilder = $object;
    }

    /**
     * @param $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);

        return $this;
    }

    /**
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                $this->order[$attr] = $dir;
            }
        } else {
            $this->order[$attribute] = $dir;
        }

        if ($attribute != 'relevance') {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * @param $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $result = [];

        if($this->searchResult !== null){
            $aggregations = $this->searchResult->getAggregations();
            if (null !== $aggregations) {
                $bucket = $aggregations->getBucket($field . RequestGenerator::BUCKET_SUFFIX);
                if ($bucket) {
                    foreach ($bucket->getValues() as $value) {
                        $metrics = $value->getMetrics();
                        $result[$metrics['value']] = $metrics;
                    }
                } else {
                    throw new StateException(__('Bucket does not exist'));
                }
            }
        }

        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _renderFilters()
    {
        $this->_filters = [];

        return parent::_renderFilters();
    }

    /**
     * @param Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function addCategoryFilter(Category $category)
    {
        $this->_categoryId = $category->getId();
        $this->addFieldToFilter('category_ids', $category->getId());

        return parent::addCategoryFilter($category);
    }

    /**
     * @param array $visibility
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);

        return parent::setVisibility($visibility);
    }

    /**
     * @throws LocalizedException
     * @throws Zend_Db_Exception
     */
    protected function _renderFiltersBefore()
    {
        $this->getCollectionClone();

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();
        $this->getSearch();

        if ($this->queryText) {
            $this->filterBuilder->setField('search_term');
            $this->filterBuilder->setValue($this->queryText);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }

        $priceRangeCalculation = $this->_scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->filterBuilder->setField('price_dynamic_algorithm');
            $this->filterBuilder->setValue($priceRangeCalculation);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }

        if ($this->_isTwoOne) {
            $this->filterBuilder->setField('category_ids');
            $this->filterBuilder->setValue($this->_categoryId);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setRequestName($this->searchRequestName);

        if(!isset($_GET['findpart']) && isset($_GET['q'])) {
            try {
                $this->searchResult = $this->getSearch()->search($searchCriteria);
            } catch (Exception $e) {
                throw new LocalizedException(__(
                    'Something went wrong, check and test your Search Engine configurations, reindex catalog and clear cache. Thrown error: %1',
                    $e->getMessage()
                ));
            }

            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = null;
            if (method_exists('$temporaryStorage', 'storeApiDocuments')) {
                $table = $temporaryStorage->storeApiDocuments($this->searchResult->getItems());
            } else {
                $table = $temporaryStorage->storeDocuments($this->searchResult->getItems());
            }

            $this->getSelect()->joinInner(
                [
                    'search_result' => $table->getName(),
                ],
                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );
            $this->getSelect()->order('e.entity_id  DESC'); // @todo may cause an atomic explosion

            if ($this->order && isset($this->orders['relevance'])) {
                $this->getSelect()
                    ->order('search_result.' . TemporaryStorage::FIELD_SCORE . ' ' . $this->orders['relevance']);
            }
        }
        parent::_renderFiltersBefore();
    }

    /**
     * @return Collection|null
     */
    public function getCollectionClone()
    {
        if ($this->collectionClone === null) {
            $this->collectionClone = clone $this;
            $this->collectionClone->setSearchCriteriaBuilder($this->searchCriteriaBuilder->cloneObject());
        }

        $searchCriterialBuilder = $this->collectionClone->getSearchCriteriaBuilder()->cloneObject();

        $collectionClone = clone $this->collectionClone;
        $collectionClone->setSearchCriteriaBuilder($searchCriterialBuilder);

        return $collectionClone;
    }

    /**
     * @return SearchInterface
     */
    private function getSearch()
    {
        if ($this->search === null) {
            $this->search = ObjectManager::getInstance()->get(SearchInterface::class);
        }

        return $this->search;
    }

}
