<?php
namespace OSC\Commands\ApiKey;

use OSC\Commands\AbstractCommand;
use Omeka\Entity\ApiKey;
use Omeka\Permissions\Acl;

class CreateCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('apikey:create', 'Create a new API key');
        $this->argument('<user-id>', 'ID of the user to associate the API key with');
        $this->argument('<label>', 'Label for the API key');
    }

    public function execute(int $userId, string $label): void
    {
        $api = $this->getOmekaInstance()->getApi();
        $em = $this->getOmekaInstance()->getServiceManager()->get('Omeka\EntityManager');

        // Check if user exists
        $userResponse = $api->read('users', $userId);
        if (!$userResponse) { // Or check if $userResponse->getTotalResults() === 0 if applicable
            throw new \InvalidArgumentException("User with ID '{$userId}' not found.");
        }
        $user = $userResponse->getContent();
        if (!$user) { // Additional check if getContent() can return null for a found user with no content
            throw new \InvalidArgumentException("User with ID '{$userId}' not found or has no content.");
        }

        // Prepare API key data
        $apiKeyData = [
            'o:owner' => ['o:id' => $userId],
            'o:label' => $label,
        ];

        // Elevate privileges to create API key
        $this->getOmekaInstance()->elevatePrivileges();

        $response = $api->create('api_keys', $apiKeyData);

        if ($response) {
            /** @var ApiKey $apiKey */
            $apiKey = $response->getContent()->getEntity();
            $this->info(sprintf(
                "API key '%s' created successfully for user ID %d.",
                $apiKey->label(),
                $apiKey->owner()->getId()
            ), true);
            $this->io()->line("Key: " . $apiKey->keyCredential());
        } else {
            throw new \ErrorException("Failed to create API key for user ID '{$userId}'.");
        }
    }
}
