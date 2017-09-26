<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzContent\EzContentInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Visible implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzContentInterface) {
            return null;
        }

        return $item->getLocation()->invisible ? 'No' : 'Yes';
    }
}
