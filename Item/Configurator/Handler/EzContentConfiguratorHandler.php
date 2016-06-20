<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler;

use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class EzContentConfiguratorHandler extends EzLocationConfiguratorHandler
{
    /**
     * Returns the content info value object from provided item.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo(ItemInterface $item)
    {
        return $item->getValue()->getContentInfo();
    }
}
