<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Customization\Customer\Observer\Override;
use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Vat;

class AfterAddressSaveObserver extends \Magento\Customer\Observer\AfterAddressSaveObserver
{
   
    protected function addValidMessage($customerAddress, $validationResult)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerId = $customerSession->getId();
        
        $customerFactory = $objectManager->create('\Magento\Customer\Model\Customer')->load($customerId);

        $customerFactory->setDisableAutoGroupChange(1);
        $customerFactory->save();

        $message = [
            (string)__('Your VAT ID was successfully validated.'),
        ];

        $customer = $customerAddress->getCustomer();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$customer->getDisableAutoGroupChange()
        ) {
            $customerVatClass = $this->_customerVat->getCustomerVatClass(
                $customerAddress->getCountryId(),
                $validationResult
            );
            $message[] = $customerVatClass == Vat::VAT_CLASS_DOMESTIC
                ? (string)__('You will be charged tax.')
                : (string)__('You will not be charged tax.');
        }

        $this->messageManager->addSuccess(implode(' ', $message));

        return $this;
    }

    
}
