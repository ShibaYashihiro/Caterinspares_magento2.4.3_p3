<?php
    namespace Customization\Customer\Observer;
    use Magento\Framework\Event\ObserverInterface;
    class AfterSaveCustomer implements ObserverInterface
    {
        protected $customer;

        protected $customerFactory;
        protected $_request;
    /**
    * @var \Magento\Framework\App\ResponseFactory
    */
   

    public function __construct(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   

        $post = $this->_request->getPost();
        $event = $observer->getEvent();
        $customer = $observer->getCustomerDataObject();
        $customerId = $customer->getId();

        $customer = $this->customer->load($customerId);

        $customerData = $customer->getDataModel();

        $customerData->setCustomAttribute('company1',$post['company']);
        $customer->updateData($customerData);
        $customerResource = $this->customerFactory->create();
        $customerResource->saveAttribute($customer, 'company1');

        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addresss = $objectManager->get('\Magento\Customer\Model\AddressFactory');

        $address = $addresss->create();

        $address->setCustomerId($customerId)

        ->setFirstname($customer->getFirstname())

        ->setLastname($customer->getLastname())

        ->setCountryId('VN')

        ->setPostcode('10000')

        //->setCity('HaNoi')

        ->setTelephone(1234567890)

        ->setFax(123456789)

        ->setCompany($post['company'])

        ->setStreet('Street')

        ->setIsDefaultBilling('1')

        ->setIsDefaultShipping('1')

        ->setSaveInAddressBook('1');

        $address->save();*/







    }
}