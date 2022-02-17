<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserIbexaBundle;

use Netgen\Bundle\ContentBrowserIbexaBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenContentBrowserIbexaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CompilerPass\IbexaDefaultPreviewPass());
    }
}
