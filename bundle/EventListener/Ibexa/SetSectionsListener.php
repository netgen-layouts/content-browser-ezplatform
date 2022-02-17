<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserIbexaBundle\EventListener\Ibexa;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\ContentBrowser\Event\ConfigLoadEvent;
use Netgen\ContentBrowser\Event\ContentBrowserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function in_array;

final class SetSectionsListener implements EventSubscriberInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [ContentBrowserEvents::CONFIG_LOAD => 'onConfigLoad'];
    }

    public function onConfigLoad(ConfigLoadEvent $event): void
    {
        if (!in_array($event->getItemType(), ['ibexa_content', 'ibexa_location'], true)) {
            return;
        }

        $config = $event->getConfig();
        if ($config->hasParameter('sections')) {
            return;
        }

        $config->setParameter(
            'sections',
            $this->configResolver->getParameter(
                'backend.ibexa.default_sections',
                'netgen_content_browser',
            ),
        );
    }
}
