<?php

namespace Dynamic\Foxy\SingleSignOn\Controller;

use SilverStripe\Control\Controller;

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
        //code here
    }
}
