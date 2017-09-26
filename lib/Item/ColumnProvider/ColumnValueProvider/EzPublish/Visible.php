<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Visible implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzPublishInterface) {
            return null;
        }

        return $item->getLocation()->invisible ? 'No' : 'Yes';
    }
}
