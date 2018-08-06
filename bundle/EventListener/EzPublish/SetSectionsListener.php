<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserBundle\EventListener\EzPublish;

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
        $config = $event->getConfig();

        if (!in_array($config->getItemType(), ['ezcontent', 'ezlocation'], true)) {
            return;
        }

        if ($config->hasParameter('sections')) {
            return;
        }

        $config->setParameter(
            'sections',
            $this->configResolver->getParameter(
                'backend.ezpublish.default_sections',
                'netgen_content_browser'
            )
        );
    }
}
