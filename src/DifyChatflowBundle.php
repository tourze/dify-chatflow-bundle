<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DifyCoreBundle\DifyCoreBundle;

final class DifyChatflowBundle extends Bundle implements BundleDependencyInterface
{
    /**
     * @return array<class-string<Bundle>, array<string, bool>>
     */
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DifyCoreBundle::class => ['all' => true],
            HttpClientBundle::class => ['all' => true],
        ];
    }
}
