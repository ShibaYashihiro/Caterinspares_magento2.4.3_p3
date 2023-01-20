<?php
/**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Index;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;
 
class Option extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
    public function __construct(Context $context, LayoutFactory $resultLayoutFactory)
    {
        $this->_resultLayoutFactory = $resultLayoutFactory;
		 
        parent::__construct($context);
    }
 
    public function execute()
    {
		return $this->_resultLayoutFactory->create();
    }
}