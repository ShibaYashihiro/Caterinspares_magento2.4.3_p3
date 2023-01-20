<?php

namespace Meetanshi\Restrictuser\Model\Plugin\Controller\Account;

use Meetanshi\Restrictuser\Helper\Data;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Controller\Account\CreatePost;

class RestrictCustomerEmail
{

    protected $urlModel;
    protected $helper;
    protected $resultRedirectFactory;
    protected $messageManager;

    public function __construct(
        UrlFactory $urlFactory,
        Data $helper,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->urlModel = $urlFactory->create();
        $this->resultRedirectFactory = $redirectFactory;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(
        CreatePost $subject,
        \Closure $proceed
    ) {
        if ($this->helper->getEnable()) {
            if ($this->helper->captchaEnable()) {
                $post = $subject->getRequest()->getPostValue();
                $token = $post['g-recaptcha-response'];
                $validation = $this->helper->validate($token);
                if (!$validation['success']) {
                    $this->messageManager->addErrorMessage($validation['error']);
                    $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setUrl($defaultUrl);
                }
            }
            $restrict = $this->helper->getDefaultValue();
            $firstnameLimit = $this->helper->getFirstnameLimit();
            $lastnameLimit = $this->helper->getLastnameLimit();
            $val = explode(',', $restrict);
            $firstname = $subject->getRequest()->getParam('firstname');
            $lasttname = $subject->getRequest()->getParam('lastname');
            $fnamelength = strlen($firstname);
            $lnamelength = strlen($lasttname);
            $email = $subject->getRequest()->getParam('email');
            list($nick, $domain) = explode('@', $email, 2);

            if (in_array($domain, $val, true) || $fnamelength > $firstnameLimit || $lnamelength > $lastnameLimit) {

                $message = __('Registration is restricted');
                if ($fnamelength > $firstnameLimit){
                    $message = __('First Name id too long.');
                }elseif ($lnamelength > $lastnameLimit){
                    $message = __('Last Name is too long.');
                }

                $this->messageManager->addErrorMessage(
                    $message
                );
                $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setUrl($defaultUrl);
            }
        }
        return $proceed();
    }
}
