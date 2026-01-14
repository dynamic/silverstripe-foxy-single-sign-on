<?php

namespace Dynamic\Foxy\SingleSignOn\Task;

use Dynamic\Foxy\API\Client\APIClient;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Member;

/**
 * Import existing Foxy customers into Silverstripe Members.
 *
 * Usage:
 *   vendor/bin/sake dev/tasks/ImportFoxyCustomersTask
 *   vendor/bin/sake dev/tasks/ImportFoxyCustomersTask dry-run=1
 */
class ImportFoxyCustomersTask extends BuildTask
{
    private static string $segment = 'ImportFoxyCustomersTask';

    protected $title = 'Import Foxy Customers';

    protected $description = 'Imports existing customers from Foxy.io into Silverstripe Members';

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function run($request): void
    {
        $dryRun = (bool) $request->getVar('dry-run');

        if ($dryRun) {
            $this->log('=== DRY RUN MODE - No changes will be saved ===');
        }

        if (!APIClient::is_valid()) {
            $this->log('ERROR: Foxy API not configured. Please check your API credentials.');
            return;
        }

        $this->log('Starting customer import from Foxy...');

        $client = APIClient::create();
        $store = $client->getCurrentStore();

        if (!$store) {
            $this->log('ERROR: Could not determine current store.');
            return;
        }

        $offset = 0;
        $limit = 100;
        $hasMore = true;

        while ($hasMore) {
            $customersUrl = $store . '/customers?limit=' . $limit . '&offset=' . $offset;
            $response = $client->getClient()->get($customersUrl);

            if (!$response || isset($response['error_message'])) {
                $this->log('ERROR: ' . ($response['error_message'] ?? 'Failed to fetch customers'));
                break;
            }

            $customers = $response['_embedded']['fx:customers'] ?? [];

            if (empty($customers)) {
                $hasMore = false;
                break;
            }

            foreach ($customers as $customerData) {
                $this->processCustomer($customerData, $dryRun);
            }

            $offset += $limit;

            // Check if there are more pages
            $hasMore = isset($response['_links']['next']);
        }

        $this->log('');
        $this->log('=== Import Complete ===');
        $this->log("Created: {$this->created}");
        $this->log("Updated: {$this->updated}");
        $this->log("Skipped: {$this->skipped}");
    }

    private function processCustomer(array $data, bool $dryRun): void
    {
        $email = $data['email'] ?? null;

        if (!$email) {
            $this->log("  SKIP: No email for customer ID {$data['id']}");
            $this->skipped++;
            return;
        }

        $foxyCustomerId = $data['id'] ?? null;
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';

        // Try to find existing member by email or Customer_ID
        $member = Member::get()->filter('Email', $email)->first();

        if (!$member && $foxyCustomerId) {
            $member = Member::get()->filter('Customer_ID', $foxyCustomerId)->first();
        }

        $isNew = false;
        if (!$member) {
            $member = Member::create();
            $isNew = true;
        }

        // Update fields
        $member->Email = $email;
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Customer_ID = $foxyCustomerId;

        // Map password if present
        if (isset($data['password_hash'])) {
            $member->Password = $data['password_hash'];

            // Extract salt if password hash is present (required for SS BlowfishEncryptor)
            if (str_starts_with($data['password_hash'], '$2y$')) {
                $member->Salt = substr($data['password_hash'], 4, 25);
            }
        }

        $member->FromDataFeed = true; // Prevent push-back to Foxy

        $action = $isNew ? 'CREATE' : 'UPDATE';
        $this->log("  {$action}: {$email} (Foxy ID: {$foxyCustomerId})");

        if (!$dryRun) {
            // Disable password encryption to prevent double-hashing
            $originalEncryption = \SilverStripe\Core\Config\Config::inst()->get(\SilverStripe\Security\Security::class, 'password_encryption_algorithm');
            \SilverStripe\Core\Config\Config::modify()->set(\SilverStripe\Security\Security::class, 'password_encryption_algorithm', 'none');

            try {
                $member->write();

                // Force encryption algo back provided it's not none
                if ($originalEncryption && $originalEncryption !== 'none') {
                    $table = 'Member';
                    $sql = "UPDATE \"$table\" SET \"PasswordEncryption\" = ? WHERE \"ID\" = ?";
                    \SilverStripe\ORM\DB::prepared_query($sql, [$originalEncryption, $member->ID]);
                }

            } finally {
                \SilverStripe\Core\Config\Config::modify()->set(\SilverStripe\Security\Security::class, 'password_encryption_algorithm', $originalEncryption);
            }
        } else {
            if ($isNew) {
                $this->created++;
            } else {
                $this->updated++;
            }
        }
    }

    private function log(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
