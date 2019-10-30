<?php

namespace Dynamic\Foxy\SingleSignOn\Factory;

use Dynamic\Foxy\Orders\Factory\FoxyFactory;
use Dynamic\Foxy\Parser\Foxy\Transaction;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;

class MemberFactory
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var
     */
    private $member;

    /**
     * OrderDetailFactory constructor.
     * @param Transaction|null $transaction
     */
    public function __construct(Transaction $transaction = null)
    {
        if ($transaction instanceof Transaction && $transaction !== null) {
            $this->setTransaction($transaction);
        }
    }

    /**
     * @param Transaction $transaction
     * @return $this
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Transaction
     */
    protected function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        if (!$this->member instanceof Member) {
            $this->setMember();
        }

        return $this->member;
    }

    protected function setMember()
    {
        /** @var ArrayData $transaction */
        $transaction = $this->getTransaction()->getParsedTransactionData()->getField('transaction');

        // if not a guest transaction in Foxy
        if (
            $transaction->getField('customer_email')
            && $transaction->getField('is_anonymous') == 0
        ) {

            /** @var $encryption - disable password encryption to prevent double encryption */
            $encryption = Config::inst()->get(Security::class, 'password_encryption_algorithm');
            Config::modify()->set(Security::class, 'password_encryption_algorithm', 'none');

            if (!$customer = Member::get()->filter('Email', $transaction->getField('customer_email'))->first()) {
                $customer = Member::create();
            }

            foreach ($this->config()->get('member_mapping') as $foxy => $ssFoxy) {
                if ($transaction->hasField($foxy)) {
                    $customer->{$ssFoxy} = $transaction->getField($foxy);
                }
            }

            $doubleWrite = $customer->isChanged('Password');
            /** flag to prevent push to Foxy on write */
            $customer->FromDataFeed = true;
            $customer->write();

            if ($doubleWrite) {
                /** flag to prevent push to Foxy on write */
                $customer->FromDataFeed = true;
                $salt = $transaction->getField('customer_password_salt');

                $customer->Salt = $salt;
                /** manuall set encryption type to sha1 */
                $customer->PasswordEncryption = 'sha1_v2.4';
                $customer->write();
            }

            /** re-enable password encryption */
            Config::modify()->set(Security::class, 'password_encryption_algorithm', $encryption);
        }
    }
}
