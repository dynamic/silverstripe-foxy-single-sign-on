<?php

namespace Dynamic\Foxy\SingleSignOn\Client;

use Dynamic\Foxy\API\Client\APIClient;
use Foxy\FoxyClient\FoxyClient;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Member;

/**
 * Class CustomerClient
 * @package Dynamic\FoxyStripe\API\Client
 */
class CustomerClient extends APIClient
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * @var bool
     */
    private static $foxy_sso_enabled = true;

    /**
     * @var array
     */
    private static $customer_map = [
        'Customer_ID' => 'id',
        'FirstName' => 'first_name',
        'Surname' => 'last_name',
        'Email' => 'email',
        'Salt' => 'password_salt',
        'Password' => 'password_hash',
    ];

    /**
     * @var string
     */
    //private static $foxy_password_hash_type = 'bcrypt';
    private static $foxy_password_hash_type = 'sha1';

    /**
     * @var Member
     */
    private $customer;

    /**
     * CustomerClient constructor.
     * @param Member $customer
     */
    public function __construct(Member $customer)
    {
        parent::__construct();

        $this->setCustomer($customer);
    }

    /**
     * @param $customer
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Member
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param bool $single
     * @return string
     */
    private function getAPIURI($single = false)
    {
        $parts = [FoxyClient::PRODUCTION_API_HOME, 'customers'];

        if ($single) {
            $parts[] = $this->getCustomer()->Customer_ID;
        }

        return implode('/', $parts);
    }

    private function getNewCustomerAPIURI()
    {
        return implode('/', [$this->getCurrentStore(), 'customers']);
    }

    /**
     * @return mixed
     */
    public function putCustomer()
    {
        $client = $this->getClient();

        if (!$this->getCustomer()->Customer_ID) {
            $response = $client->post($this->getNewCustomerAPIURI(), $this->getSendData());
        } else {
            $response = $client->patch($this->getAPIURI(true), $this->getSendData());
        }

        return $response;
    }

    /**
     * @return mixed
     */
    public function fetchCustomer()
    {
        $client = $this->getClient();

        $result = $client->get($this->getAPIURI(true));

        return $result;
    }

    /**
     * @return mixed
     */
    public function fetchCustomers()
    {
        $client = $this->getClient();

        $result = $client->get($this->getAPIURI(true));

        return $result;
    }

    /**
     *
     */
    public function deleteCustomer()
    {
    }

    /**
     * @return array
     */
    public function getSendData()
    {
        $data = [];

        if ($customer = $this->getCustomer()) {
            foreach ($this->config()->get('customer_map') as $localField => $remoteField) {
                if ($customer->{$localField}) {
                    $data[$remoteField] = $customer->{$localField};
                }
            }
        }

        return $data;
    }
}
