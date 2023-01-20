<?php

namespace Meetanshi\Restrictuser\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const ENABLE_DISABLE = 'restrictuser/general/enabled';
    const RESTRICT = 'restrictuser/general/restrictusers';
    const FNAMELIMIT = 'restrictuser/general/firstname_limit';
    const LNAMELIMIT = 'restrictuser/general/lastname_limit';
    const CAPTCHAENABLE = 'restrictuser/captcha/enabled';
    const SITEKEY = 'restrictuser/captcha/sitekey';
    const SECRETKEY = 'restrictuser/captcha/secretkey';

    protected $scopeConfig;
    protected $productFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getEnable()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_DISABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultValue()
    {
        return $this->scopeConfig->getValue(
            self::RESTRICT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getFirstnameLimit()
    {
        return $this->scopeConfig->getValue(
            self::FNAMELIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLastnameLimit()
    {
        return $this->scopeConfig->getValue(
            self::LNAMELIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function captchaEnable()
    {
        return $this->scopeConfig->getValue(
            self::CAPTCHAENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function captchaSiteKey()
    {
        return $this->scopeConfig->getValue(
            self::SITEKEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function validate($token)
    {
        $verification = [
            'success' => false,
            'error' => 'The request is invalid or malformed.'
        ];
        if ($token) {
            try {
                $secret = $this->scopeConfig->getValue(self::SECRETKEY, ScopeInterface::SCOPE_STORE);
                $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' .
                    $secret . '&response=' . $token . '&remoteip=' . $_SERVER["REMOTE_ADDR"];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);

                $result = json_decode($response, true);

                if ($result['success']) {
                    $verification['success'] = true;
                } elseif (array_key_exists('error-codes', $result)) {
                    $verification['error'] = $this->getErrorMessage($result['error-codes'][0]);
                }
            } catch (\Exception $e) {
                $verification['error'] = __($e->getMessage());
            }
        }

        return $verification;
    }

    private function getErrorMessage($errorCode)
    {
        $errorCodesGoogle = [
            'missing-input-secret' => __('The secret parameter is missing.'),
            'invalid-input-secret' => __('The secret parameter is invalid or malformed.'),
            'missing-input-response' => __('The response parameter is missing.'),
            'invalid-input-response' => __('The response parameter is invalid or malformed.'),
            'bad-request' => __('The request is invalid or malformed.')
        ];

        if (array_key_exists($errorCode, $errorCodesGoogle)) {
            return $errorCodesGoogle[$errorCode];
        }

        return __('Something is wrong.');
    }
}
