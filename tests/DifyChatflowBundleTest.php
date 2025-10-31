<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyChatflowBundle\DifyChatflowBundle;
use Tourze\DifyCoreBundle\DifyCoreBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DifyChatflowBundle::class)]
#[RunTestsInSeparateProcesses]
final class DifyChatflowBundleTest extends AbstractBundleTestCase
{
    public function testGetBundleDependencies(): void
    {
        $dependencies = DifyChatflowBundle::getBundleDependencies();

        self::assertIsArray($dependencies);
        self::assertArrayHasKey(DoctrineBundle::class, $dependencies);
        self::assertArrayHasKey(DifyCoreBundle::class, $dependencies);
        self::assertArrayHasKey(HttpClientBundle::class, $dependencies);

        self::assertSame(['all' => true], $dependencies[DoctrineBundle::class]);
        self::assertSame(['all' => true], $dependencies[DifyCoreBundle::class]);
        self::assertSame(['all' => true], $dependencies[HttpClientBundle::class]);
    }
}
