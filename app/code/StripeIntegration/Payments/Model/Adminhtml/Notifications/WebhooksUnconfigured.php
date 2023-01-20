<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Notifications;

class WebhooksUnconfigured implements \Magento\Framework\Notification\MessageInterface
{
    public $configurations = null;
    public $displayedText = null;
    public $stripeWebhooksConfigurationLink = "https://stripe.com/docs/magento/configuration#webhooks";

    public function __construct(
        \StripeIntegration\Payments\Logger\Handler $logHandler,
        \StripeIntegration\Payments\Model\Webhook $webhookModel,
        \StripeIntegration\Payments\Model\ResourceModel\Webhook\Collection $webhooksCollection,
        \StripeIntegration\Payments\Helper\WebhooksSetup $webhooksSetup,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->logHandler = $logHandler;
        $this->webhookModel = $webhookModel;
        $this->webhooksCollection = $webhooksCollection;
        $this->storeManager = $storeManager;

        $stores = $this->storeManager->getStores();
        $configurations = array();

        foreach ($stores as $storeId => $store)
        {
            $configurations[] = $webhooksSetup->getStoreViewAPIKey($store, 'test');
            $configurations[] = $webhooksSetup->getStoreViewAPIKey($store, 'live');
        }

        $configurations = $webhooksSetup->getStoreViewAPIKeys();
        $allWebhooks = $this->webhooksCollection->getAllWebhooks();

        if ($allWebhooks->count() == 0)
        {
            $this->displayedText = "<strong>Warning!</strong> An initial configuration of Stripe Webhooks is necessary from Stores &rarr; Configuration &rarr; Sales &rarr; Payment Methods &rarr; Stripe &rarr; Basic Settings &rarr; Webhooks.";

            return;
        }

        $activePublishableKeys = [];
        $duplicateWebhookPublishableKeys = [];
        $staleWebhookPublishableKeys = [];
        $inactiveStores = [];
        $duplicateWebhookStores = [];
        $staleWebhookStores = [];

        // Figure out active, duplicate and stale webhooks
        foreach ($allWebhooks as $webhook)
        {
            $key = $webhook->getPublishableKey();

            $createdAtTimestamp = strtotime($webhook->getCreatedAt());
            $wasJustCreated = ((time() - $createdAtTimestamp) <= 300);
            $inactivityPeriod = (time() - $webhook->getLastEvent());
            if ($webhook->getActive() > 0 || ($webhook->getActive() == 0 && $wasJustCreated))
                $activePublishableKeys[$key] = $key;

            if ($webhook->getActive() > 1)
                $duplicateWebhookPublishableKeys[$key] = $key;

            $sixHours = 6 * 60 * 60;
            if ($webhook->getActive() > 0 && $inactivityPeriod > $sixHours && !$wasJustCreated)
                $staleWebhookPublishableKeys[$key] = $key;
        }

        foreach ($configurations as $configuration)
        {
            if (!empty($configuration['api_keys']['pk']) && !in_array($configuration['api_keys']['pk'], $activePublishableKeys))
                $inactiveStores[] = $configuration;

            if (in_array($configuration['api_keys']['pk'], $duplicateWebhookPublishableKeys))
                $duplicateWebhookStores[] = $configuration;

            if (in_array($configuration['api_keys']['pk'], $staleWebhookPublishableKeys))
                $staleWebhookStores[] = $configuration;
        }

        if (!empty($inactiveStores))
        {
            $storeNames = [];

            foreach ($inactiveStores as $store) {
                $storeNames[] = $store['label'] . " (" . $store['mode_label'] . ")";
            }

            $storeNamesText = implode(", ", $storeNames);

            $this->displayedText = "<strong>Warning!</strong> Stripe Webhooks have not yet been configured for: $storeNamesText - You can configure them from Stores &rarr; Configuration &rarr; Sales &rarr; Payment Methods &rarr; Stripe &rarr; Basic Settings &rarr; Webhooks.";

            return;
        }

        if (!empty($duplicateWebhookStores))
        {
            $storeNames = [];

            foreach ($duplicateWebhookStores as $store) {
                $storeNames[] = $store['label'] . " (" . $store['mode_label'] . ")";
            }

            $storeNamesText = implode(", ", $storeNames);

            $this->displayedText = "<strong>Warning!</strong> Duplicate webhooks configuration detected for: $storeNamesText - Please ensure that you only have a single webhook configured per Stripe account.";

            return;
        }

        if (!empty($staleWebhookStores))
        {
            $storeNames = [];

            foreach ($staleWebhookStores as $store) {
                $storeNames[] = $store['label'] . " (" . $store['mode_label'] . ")";
            }

            $storeNamesText = implode(", ", $storeNames);

            $this->displayedText = "<strong>Warning!</strong> No webhook events have been received for: $storeNamesText - Please ensure that your webhooks URL is externally accessible and your cron jobs are running.";

            return;
        }
    }

    public function getIdentity()
    {
        return 'stripe_payments_notification_webhooks_unconfigured';
    }

    public function isDisplayed()
    {
        return !empty($this->displayedText);
    }

    public function getText()
    {
        return $this->displayedText;
    }

    public function getSeverity()
    {
        // SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}
