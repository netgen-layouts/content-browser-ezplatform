<?php

namespace Netgen\ContentBrowser\Item\Serializer\Handler;

use Netgen\ContentBrowser\Item\ItemInterface;

class EzContentSerializerHandler extends EzLocationSerializerHandler
{
    /**
     * Returns the content info value object from provided item.
     *
     * @param \Netgen\ContentBrowser\Item\ItemInterface $item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo(ItemInterface $item)
    {
        return $item->getContentInfo();
    }
}
