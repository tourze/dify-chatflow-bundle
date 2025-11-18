<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyChatflowBundle\DifyChatflowBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DifyChatflowBundle::class)]
#[RunTestsInSeparateProcesses]
final class DifyChatflowBundleTest extends AbstractBundleTestCase
{
}
