<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform;

use Netgen\ContentBrowser\Ez\Item\EzPlatform\EzPlatformInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class ContentType implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzPlatformInterface) {
            return null;
        }

        $contentType = $item->getContent()->getContentType();

        return $contentType->getName() ?? '';
    }
}
