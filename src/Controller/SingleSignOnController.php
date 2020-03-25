<?php

namespace Dynamic\Foxy\SingleSignOn\Controller;

use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class SingleSignOnController
 * @package Dynamic\Foxy\SingleSignOn\Controller
 */
class SingleSignOnController extends Controller
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '' => 'sso',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'sso',
    ];

    /**
     * @param $request
     */
    public function sso($request)
    {
        // GET variables from FoxyCart Request
        $fcsid = $this->request->getVar('fcsid');
        $timestampNew = strtotime('+30 days');
        $helper = FoxyHelper::create();

        // get current member if logged in. If not, create a 'fake' user with Customer_ID = 0
        // fake user will redirect to FC checkout, ask customer to log in
        // to do: consider a login/registration form here if not logged in
        if (!$Member = Security::getCurrentUser()) {
            $Member = new Member();
            $Member->Customer_ID = 0;
        }

        $auth_token = sha1($Member->Customer_ID . '|' . $timestampNew . '|' . $helper->getStoreSecret());

        $params = [
            'fc_auth_token' => $auth_token,
            'fcsid' => $fcsid,
            'fc_customer_id' => $Member->Customer_ID,
            'timestamp' => $timestampNew,
        ];

        $httpQuery = http_build_query($params);

        // the '/' between the url and checkout is included from the foxy helper
        $this->redirect("{$helper::StoreURL()}checkout?$httpQuery");
    }
}
