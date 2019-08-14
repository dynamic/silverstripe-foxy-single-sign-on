<?php

namespace Dynamic\Foxy\SingleSignOn\Extension;

use Dynamic\Foxy\Parser\Foxy\Transaction;
use Dynamic\Foxy\SingleSignOn\Factory\MemberFactory;
use SilverStripe\Core\Extension;

class FoxyControllerMemberExtension extends Extension
{
    /**
     * @param Transaction $transaction
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function doAdditionalParse(Transaction $transaction)
    {
        MemberFactory::create($transaction)->getMember();
    }
}
