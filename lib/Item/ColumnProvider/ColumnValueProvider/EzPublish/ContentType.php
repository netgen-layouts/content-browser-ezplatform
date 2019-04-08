<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use Netgen\ContentBrowser\Ez\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class ContentType implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzPublishInterface) {
            return null;
        }

        $contentType = $item->getContent()->getContentType();

        return $contentType->getName() ?? '';
    }
}
