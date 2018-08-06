<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserBundle\EventListener\EzPublish;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\ContentBrowser\Event\ConfigLoadEvent;
use Netgen\ContentBrowser\Event\ContentBrowserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SetLocationContentTypesListener implements EventSubscriberInterface
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
        $config = $event->getConfig();

        if (!in_array($config->getItemType(), ['ezcontent', 'ezlocation'], true)) {
            return;
        }

        if ($config->hasParameter('location_content_types')) {
            return;
        }

        $config->setParameter(
            'location_content_types',
            $this->configResolver->getParameter(
                'backend.ezpublish.location_content_types',
                'netgen_content_browser'
            )
        );
    }
}
