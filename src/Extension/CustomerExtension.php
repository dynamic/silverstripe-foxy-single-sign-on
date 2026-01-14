<?php

namespace Dynamic\Foxy\SingleSignOn\Extension;

use Dynamic\Foxy\API\Client\APIClient;
use Dynamic\Foxy\SingleSignOn\Client\CustomerClient;
use SilverStripe\ORM\DataExtension;

/**
 * Extension to sync Member data with Foxy.io customer records
 */
class CustomerExtension extends DataExtension
{
    private static array $db = [
        'Customer_ID' => 'Int',
    ];

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        // Skip sync if this write is from a Foxy data feed (prevents infinite loop)
        if ($this->owner->FromDataFeed ?? false) {
            return;
        }

        if ($this->isValidAPI()) {
            $client = CustomerClient::create($this->owner);
            $data = $client->putCustomer();

            // Extract Customer_ID from response if this is a new customer
            if (!empty($data) && !$this->owner->Customer_ID) {
                if (isset($data['_links']['self']['href'])) {
                    $parts = explode('/', $data['_links']['self']['href']);
                    $customerID = end($parts);

                    if (is_numeric($customerID)) {
                        $this->owner->Customer_ID = (int) $customerID;
                    }
                }
            }
        }
    }

    protected function isValidAPI(): bool
    {
        return (bool) (
            APIClient::config()->get('enable_api')
            && CustomerClient::config()->get('foxy_sso_enabled')
            && APIClient::is_valid()
        );
    }
}

