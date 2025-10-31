<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\DependencyInjection\DifyChatflowExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DifyChatflowExtension::class)]
final class DifyChatflowExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testAlias(): void
    {
        $extension = new DifyChatflowExtension();

        self::assertSame('dify_chatflow', $extension->getAlias());
    }
}
