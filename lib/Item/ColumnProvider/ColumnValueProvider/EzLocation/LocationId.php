<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzLocation;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzLocation\EzLocationInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class LocationId implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzLocationInterface) {
            return null;
        }

        return $item->getLocation()->id;
    }
}
