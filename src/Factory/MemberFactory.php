<?php

namespace Dynamic\Foxy\SingleSignOn\Factory;

use Dynamic\Foxy\Parser\Foxy\Transaction;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;

/**
 * Factory for creating/updating Member records from Foxy transaction data
 */
class MemberFactory
{
    use Configurable;
    use Extensible;
    use Injectable;

    private ?Transaction $transaction = null;

    private ?Member $member = null;

    public function __construct(?Transaction $transaction = null)
    {
        if ($transaction instanceof Transaction) {
            $this->setTransaction($transaction);
        }
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    protected function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function getMember(): ?Member
    {
        if (!$this->member instanceof Member) {
            $this->setMember();
        }

        return $this->member;
    }

    protected function setMember(): void
    {
        $transactionData = $this->getTransaction()?->getParsedTransactionData();
        if (!$transactionData) {
            return;
        }

        /** @var ArrayData $transaction */
        $transaction = $transactionData->getField('transaction');

        // Skip guest transactions in Foxy
        $email = $transaction->getField('customer_email');
        $isAnonymous = $transaction->getField('is_anonymous');

        if (!$email || $isAnonymous == 1) {
            return;
        }

        // Disable password encryption to prevent double-hashing
        // (Foxy sends pre-hashed bcrypt passwords)
        $originalEncryption = Config::inst()->get(Security::class, 'password_encryption_algorithm');
        Config::modify()->set(Security::class, 'password_encryption_algorithm', 'none');

        try {
            // Find or create member by email
            $customer = Member::get()->filter('Email', $email)->first();
            if (!$customer) {
                $customer = Member::create();
            }

            // Map Foxy fields to Member fields
            foreach ($this->config()->get('member_mapping') as $foxyField => $memberField) {
                if ($transaction->hasField($foxyField)) {
                    $customer->{$memberField} = $transaction->getField($foxyField);
                }
            }

            // Extract salt if password hash is present (required for SS BlowfishEncryptor)
            if ($transaction->hasField('password_hash')) {
                $hash = $transaction->getField('password_hash');
                if ($hash && str_starts_with($hash, '$2y$')) {
                    $customer->Salt = substr($hash, 4, 25);
                }
            }

            // Flag to prevent push back to Foxy on write
            $customer->FromDataFeed = true;

            // With bcrypt, the hash includes the algorithm info and salt,
            // so Silverstripe can validate it directly with password_verify()
            $customer->write();

            // Force the encryption algorithm back to the site default in the database
            // This ensures Silverstripe knows how to validate the bcrypt hash (usually 'blowfish')
            // but prevents the double-hashing that would occur if we wrote it with that algorithm active.
            if ($originalEncryption && $originalEncryption !== 'none') {
                $table = 'Member';
                $sql = "UPDATE \"$table\" SET \"PasswordEncryption\" = ? WHERE \"ID\" = ?";
                \SilverStripe\ORM\DB::prepared_query($sql, [$originalEncryption, $customer->ID]);
            }

            $this->member = $customer;
        } finally {
            // Re-enable password encryption
            Config::modify()->set(Security::class, 'password_encryption_algorithm', $originalEncryption);
        }
    }
}

