<?php

namespace Dynamic\Foxy\SingleSignOn\Client;

use Dynamic\Foxy\API\Client\APIClient;
use Foxy\FoxyClient\FoxyClient;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Member;

/**
 * Client for syncing customer data with Foxy.io API
 */
class CustomerClient extends APIClient
{
    use Configurable;
    use Extensible;
    use Injectable;

    private static bool $foxy_sso_enabled = true;

    /**
     * Mapping of Silverstripe Member fields to Foxy customer fields.
     * Note: Salt is not needed for bcrypt as it's embedded in the hash.
     */
    private static array $customer_map = [
        'Customer_ID' => 'id',
        'FirstName' => 'first_name',
        'Surname' => 'last_name',
        'Email' => 'email',
        'Password' => 'password_hash',
    ];

    /**
     * Password hash type for Foxy.io - must match store settings.
     * Options: bcrypt, sha1, phpass, drupal, etc.
     * See: https://api.foxycart.com/rels/customer_password_hash_types
     */
    private static string $foxy_password_hash_type = 'bcrypt';

    /**
     * BCrypt cost factor (default 10, Foxy default 15)
     */
    private static int $foxy_password_hash_config = 10;

    private ?Member $customer = null;

    public function __construct(Member $customer)
    {
        parent::__construct();

        $this->setCustomer($customer);
    }

    public function setCustomer(Member $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCustomer(): ?Member
    {
        return $this->customer;
    }

    private function getAPIURI(bool $single = false): string
    {
        $parts = [FoxyClient::PRODUCTION_API_HOME, 'customers'];

        if ($single) {
            $parts[] = $this->getCustomer()?->Customer_ID;
        }

        return implode('/', $parts);
    }

    private function getNewCustomerAPIURI(): string
    {
        return implode('/', [$this->getCurrentStore(), 'customers']);
    }

    public function putCustomer(): mixed
    {
        $client = $this->getClient();

        if (!$this->getCustomer()?->Customer_ID) {
            $response = $client->post($this->getNewCustomerAPIURI(), $this->getSendData());
        } else {
            $response = $client->patch($this->getAPIURI(true), $this->getSendData());
        }

        return $response;
    }

    public function fetchCustomer(): mixed
    {
        $client = $this->getClient();

        return $client->get($this->getAPIURI(true));
    }

    public function fetchCustomers(): mixed
    {
        $client = $this->getClient();

        return $client->get($this->getAPIURI(true));
    }

    public function deleteCustomer(): void
    {
        // Not yet implemented
    }

    /**
     * Build data payload for Foxy API from Member fields
     */
    public function getSendData(): array
    {
        $data = [];

        if ($customer = $this->getCustomer()) {
            foreach ($this->config()->get('customer_map') as $localField => $remoteField) {
                if ($customer->{$localField}) {
                    $data[$remoteField] = $customer->{$localField};
                }
            }

            // Add password hash type and config for Foxy
            $data['password_hash_type'] = $this->config()->get('foxy_password_hash_type');
            $data['password_hash_config'] = $this->config()->get('foxy_password_hash_config');
        }

        return $data;
    }
}

