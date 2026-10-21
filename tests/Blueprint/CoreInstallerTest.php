<?php
namespace Tests\Blueprint;

use Exception;
use OSC\Commands\AbstractCommand;
use OSC\Commands\Blueprint\CoreInstaller;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoreInstaller::class)]
class CoreInstallerTest extends TestCase
{
    /**
     * Exit code of `core:status --is-installed`, --force, and the expected error (null: deployable).
     */
    public static function syncCases(): array
    {
        return [
            'installed, --force' => [0, true, null],
            'installed, no --force' => [0, false, 'Pass --force to deploy onto it'],
            'not installed' => [1, true, "Omit '--skip core' to install it"],
            'status failed' => [255, true, 'Could not determine whether Omeka S is installed at /omeka (exit code 255)'],
        ];
    }

    #[DataProvider('syncCases')]
    public function testAssertExistingInstallDeployable(int $exitCode, bool $force, ?string $error): void
    {
        $command = $this->createMock(AbstractCommand::class);
        $command->method('resolveOmekaPath')->willReturn('/omeka');
        $command->expects($this->once())->method('runInNewProcess')
            ->with(['core:status', '--is-installed'])->willReturn($exitCode);

        if ($error !== null) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($error);
        }
        (new CoreInstaller($command))->assertExistingInstallDeployable($force);
    }
}
