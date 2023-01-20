<?php
namespace Meetanshi\Restrictuser\Block;

use Meetanshi\Restrictuser\Helper\Data;
use Magento\Framework\View\Element\Template;

class RestrictUser extends Template
{
    protected $moduleHelper;

    public function __construct(
        Template\Context $context,
        Data $moduleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleHelper = $moduleHelper;
    }
    public function captchaEnable()
    {
        return $this->moduleHelper->captchaEnable();
    }
    public function captchaSiteKey()
    {
        return $this->moduleHelper->captchaSiteKey();
    }
}
