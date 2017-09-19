<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class ContentId implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        return $item->getLocation()->contentId;
    }
}
