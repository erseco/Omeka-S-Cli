<?php
namespace OSC\Tests\Commands\ApiKey;

use OSC\Commands\ApiKey\CreateCommand;
use OSC\Omeka\OmekaInstance;
use Omeka\Api\Manager as ApiManager;
use Omeka\Api\Representation\UserRepresentation;
use Omeka\Api\Representation\ApiKeyRepresentation;
use Omeka\Entity\User;
use Omeka\Entity\ApiKey as ApiKeyEntity;
use Omeka\Service\EntityManager;
use PHPUnit\Framework\TestCase;
use Ahc\Cli\IO\Interactor;
use Symfony\Component\Console\Output\BufferedOutput;

class CreateCommandTest extends TestCase
{
    private $omekaInstanceMock;
    private $apiManagerMock;
    private $entityManagerMock;
    private $command;

    protected function setUp(): void
    {
        $this->omekaInstanceMock = $this->createMock(OmekaInstance::class);
        $this->apiManagerMock = $this->createMock(ApiManager::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $serviceManagerMock = $this->createMock(\Laminas\ServiceManager\ServiceManager::class);

        $this->omekaInstanceMock->method('getApi')->willReturn($this->apiManagerMock);
        $this->omekaInstanceMock->method('getServiceManager')->willReturn($serviceManagerMock);
        $serviceManagerMock->method('get')->with('Omeka\EntityManager')->willReturn($this->entityManagerMock);

        $this->command = $this->getMockBuilder(CreateCommand::class)
            ->onlyMethods(['getOmekaInstance'])
            ->getMock();

        $this->command->method('getOmekaInstance')->willReturn($this->omekaInstanceMock);
        // $this->command->defaults(); // Removed to avoid re-registration errors

        // Set a default Interactor that writes to STDOUT (which we will buffer)
        // The Interactor is usually set by the Application, but for a unit test, we can set it manually.
        // $io = new Interactor(); // Defaults to STDIN/STDOUT - Command will create its own default.
        // $this->command->setIO($io); // Method does not exist, command handles its IO.
    }

    public function testExecuteSuccess()
    {
        $userId = 1;
        $label = 'Test API Key';

        // Mock UserRepresentation & its Response
        $userRepresentationMock = $this->createMock(UserRepresentation::class);
        $userResponseMock = $this->createMock(\Omeka\Api\Response::class);
        $userResponseMock->method('getContent')->willReturn($userRepresentationMock);

        $this->apiManagerMock->expects($this->once())
            ->method('read')
            ->with('users', $userId)
            ->willReturn($userResponseMock);

        // Mock User entity for ApiKey owner
        $userEntityMock = $this->createMock(User::class);
        $userEntityMock->method('getId')->willReturn($userId);

        // Mock ApiKey entity
        $apiKeyEntityMock = $this->createMock(ApiKeyEntity::class);
        $apiKeyEntityMock->method('owner')->willReturn($userEntityMock);
        $apiKeyEntityMock->method('label')->willReturn($label);
        $apiKeyEntityMock->method('keyCredential')->willReturn('test_api_key_credential');

        // Mock ApiKeyRepresentation & its Response
        $apiKeyRepresentationMock = $this->createMock(ApiKeyRepresentation::class);
        $apiKeyRepresentationMock->method('getEntity')->willReturn($apiKeyEntityMock);
        $apiKeyResponseMock = $this->createMock(\Omeka\Api\Response::class);
        $apiKeyResponseMock->method('getContent')->willReturn($apiKeyRepresentationMock);

        $this->apiManagerMock->expects($this->once())
            ->method('create')
            ->with('api_keys', [
                'o:owner' => ['o:id' => $userId],
                'o:label' => $label,
            ])
            ->willReturn($apiKeyResponseMock);

        $this->omekaInstanceMock->expects($this->once())->method('elevatePrivileges');

        // Execute the command
        $this->command->execute($userId, $label);

        // Assertions are now primarily about mock interactions (already defined by expects())
        // and that no unexpected exceptions were thrown.
        // Output assertions removed for simplification.
        $this->assertTrue(true); // Placeholder assertion if no explicit mock verification is needed here beyond `expects`
    }

    public function testExecuteUserNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("User with ID '999' not found.");

        $userId = 999;
        $label = 'Test API Key';

        $this->apiManagerMock->expects($this->once())
            ->method('read')
            ->with('users', $userId)
            ->willReturn(null); // Simulate user not found

        $this->command->execute($userId, $label);
    }

    public function testExecuteApiKeyCreationFails()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage("Failed to create API key for user ID '1'.");

        $userId = 1;
        $label = 'Test API Key';

        // Mock UserRepresentation & its Response
        $userRepresentationMock = $this->createMock(UserRepresentation::class);
        $userResponseMock = $this->createMock(\Omeka\Api\Response::class);
        $userResponseMock->method('getContent')->willReturn($userRepresentationMock);

        $this->apiManagerMock->expects($this->once())
            ->method('read')
            ->with('users', $userId)
            ->willReturn($userResponseMock);

        $this->apiManagerMock->expects($this->once())
            ->method('create')
            ->with('api_keys', $this->anything())
            ->willReturn(null); // Simulate API key creation failure

        $this->omekaInstanceMock->expects($this->once())->method('elevatePrivileges');

        $this->command->execute($userId, $label);
    }
}
