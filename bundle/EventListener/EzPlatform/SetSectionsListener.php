<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserEzPlatformBundle\EventListener\EzPlatform;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\ContentBrowser\Event\ConfigLoadEvent;
use Netgen\ContentBrowser\Event\ContentBrowserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SetSectionsListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

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
        if (!in_array($event->getItemType(), ['ezcontent', 'ezlocation'], true)) {
            return;
        }

        $config = $event->getConfig();
        if ($config->hasParameter('sections')) {
            return;
        }

        $config->setParameter(
            'sections',
            $this->configResolver->getParameter(
                'backend.ezplatform.default_sections',
                'netgen_content_browser'
            )
        );
    }
}
