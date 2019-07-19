<?php

namespace Dynamic\Foxy\SingleSignOn\Extension;

use SilverStripe\Admin\LeftAndMainExtension;

/**
 * Class FoxySingleSignOnExtension
 * @package Dynamic\Foxy\SingleSignOn\Extension
 */
class FoxySingleSignOnExtension extends LeftAndMainExtension
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'syncFromFoxy',
    ];

    /**
     *
     */
    public function syncFromFoxy()
    {
        //sync method here
    }
}
