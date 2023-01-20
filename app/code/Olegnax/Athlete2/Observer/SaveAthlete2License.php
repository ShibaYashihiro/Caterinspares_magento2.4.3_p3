<?php

/**
 * Save Athlete2 Settings interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SaveAthlete2License implements ObserverInterface {

	/**
	 * 
	 *
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $_messageManager;

	/**
	 * 
	 *
	 * @var \Olegnax\Athlete2\Helper\Helper
	 */
	protected $_helper;

	/**
	 * Constructor
	 *
	 * @param \Olegnax\Athlete2\Model\DynamicStyle\Generator $generator
	 */
	public function __construct(
	\Magento\Framework\Message\ManagerInterface $messageManager, \Olegnax\Athlete2\Helper\Helper $helper ) {
		$this->_messageManager	 = $messageManager;
		$this->_helper			 = $helper;
	}

	/**
	 * @param Observer $observer
	 * @return void
	 */
	public function execute( Observer $observer ) {
		$this->_messageManager->getMessages( true );
		$check = $this->_helper->checkPurchaseCode( true );
		if ( 'remove' == $check ) {
			$this->_messageManager->addSuccess( 'Athlete2 Theme License was removed!' );
		} elseif ( 'valid' == $check ) {
			$this->_messageManager->addSuccess( 'Athlete2 Theme was activated!' );
		} else {
			$this->_messageManager->addWarning( 'Purchase code is not valid!' );
		}
	}

}
