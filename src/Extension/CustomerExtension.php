<?php

namespace Dynamic\Foxy\SingleSignOn\Extension;

use Dynamic\Foxy\API\Client\APIClient;
use Dynamic\Foxy\SingleSignOn\Client\CustomerClient;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataExtension;

/**
 * Class CustomerExtension
 * @package Dynamic\Foxy\SingleSignOn\Extension
 */
class CustomerExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'Customer_ID' => 'Int',
    ];

    /**
     * @var array
     */
    /*private static $indexes = [
        'foxy-single-sign-on' => [
            'type' => 'unique',
            'columns' => ['Customer_ID'],
        ],
    ];*/

    /**
     * @param FieldList $actions
     */
    public function updateCMSActions(FieldList $actions)
    {
        $actions->push(FormAction::create('syncFromFoxy')->setTitle('Sync Customer From Foxy'));
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->isValidAPI()) {
            $client = CustomerClient::create($this->owner);
            $data = $client->putCustomer();

            if (!$this->owner->Customer_ID) {
                $parts = explode('/', $data['_links']['self']['href']);

                $customerID = $parts[count($parts) - 1];

                $this->owner->Customer_ID = $customerID;
            }
        }
    }

    /**
     * @return bool
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function isValidAPI()
    {
        return APIClient::config()->get('enable_api')
            && CustomerClient::config()->get('foxy_sso_enabled')
            && APIClient::is_valid();
    }
}
