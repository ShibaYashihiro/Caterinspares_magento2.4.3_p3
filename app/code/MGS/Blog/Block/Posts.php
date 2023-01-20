<?php

namespace MGS\Blog\Block;

use Magento\Customer\Model\Context as CustomerContext;

class Posts extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $httpContext;
    protected $orderedCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {
        $this->_post = $post;
        $this->_coreRegistry = $registry;
        $this->_blogHelper = $blogHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }
	
	public function getCurrentUrl() {
		$url = $this->_storeManager->getStore()->getCurrentUrl(false);
		$urlArr = explode('?', $url);
		return $urlArr[0];
	}

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();
        $post = $this->_post;
        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId());
        
		if($this->getRequest()->getFullActionName() == 'blog_index_index') {
			if(!$this->orderedCollection){
				$sortBy = 'created_at';
				$directionBy = 'ASC';
				if($sort = $this->getRequest()->getParam('sort', false)) {
					$sortBy = $sort;
				}
				if($direction = $this->getRequest()->getParam('direction', false)) {
					$directionBy = $direction;
				}
				$postCollection->getSelect()->order('main_table.'.$sortBy.' '.$directionBy);
				$this->orderedCollection = $postCollection;
			}
			$postCollection = $this->orderedCollection;
		}else{
			$postCollection
            ->setOrder('main_table.created_at', $this->getConfig('general_settings/default_sort'));
		}
		
		
		$this->setCollection($postCollection);
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POST_LIST',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $pageTitle = $this->_blogHelper->getConfig('general_settings/title');
        $breadcrumbsBlock->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbsBlock->addCrumb(
            'blog',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => ''
            ]
        );
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        $collection = $this->_collection;
		return $collection;
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        $pageTitle = $this->getConfig('general_settings/title');
        $metaKeywords = $this->getConfig('general_settings/meta_keywords');
        $metaDescription = $this->getConfig('general_settings/meta_description');
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-list');
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        if ($this->getCollection()) {
            $pagerArray = [10 => 10, 20 => 20, 50 => 50];
			$pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'blog.post.list.pager'
            )
			->setAvailableLimit($pagerArray)
			//->setLimit(10)
			->setCollection(
                $this->getCollection()
            )->setTemplate('MGS_Blog::html/pager.phtml');;
            $this->setChild('pager', $pager);
        }
        return parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
