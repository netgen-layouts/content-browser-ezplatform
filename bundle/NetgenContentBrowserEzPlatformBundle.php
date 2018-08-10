<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserEzPlatformBundle;

use Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenContentBrowserEzPlatformBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CompilerPass\EzPublishDefaultPreviewPass());
    }
}
