<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Netgen\ContentBrowser\Ibexa\Item\Ibexa\IbexaInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Priority implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof IbexaInterface) {
            return null;
        }

        return (string) $item->getLocation()->priority;
    }
}
