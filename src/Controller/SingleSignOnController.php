<?php

namespace Dynamic\Foxy\SingleSignOn\Controller;

use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Controller handling Single Sign-On requests from Foxy.io checkout
 */
class SingleSignOnController extends Controller
{
    private static array $url_handlers = [
        '' => 'sso',
    ];

    private static array $allowed_actions = [
        'sso',
    ];

    /**
     * Handle SSO request from Foxy checkout.
     * Generates an auth token and redirects to Foxy checkout with authentication.
     */
    public function sso(HTTPRequest $request): HTTPResponse
    {
        // GET variables from FoxyCart Request
        $fcsid = $request->getVar('fcsid');
        $timestampNew = strtotime('+30 days');
        $helper = FoxyHelper::create();

        // Get current member if logged in, otherwise create 'fake' user with Customer_ID = 0
        // This will redirect to FC checkout and ask customer to log in
        $member = Security::getCurrentUser();
        if (!$member) {
            $member = Member::create();
            $member->Customer_ID = 0;
        }

        // Generate auth token per Foxy SSO spec
        // See: https://wiki.foxycart.com/v/2.0/sso
        $auth_token = sha1($member->Customer_ID . '|' . $timestampNew . '|' . $helper->getStoreSecret());

        $params = [
            'fc_auth_token' => $auth_token,
            'fcsid' => $fcsid,
            'fc_customer_id' => $member->Customer_ID,
            'timestamp' => $timestampNew,
        ];

        $httpQuery = http_build_query($params);

        // Redirect to Foxy checkout with auth params
        return $this->redirect($helper::StoreURL() . "checkout?{$httpQuery}");
    }
}

