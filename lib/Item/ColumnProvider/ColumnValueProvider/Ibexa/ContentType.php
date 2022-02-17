<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Netgen\ContentBrowser\Ibexa\Item\Ibexa\IbexaInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class ContentType implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof IbexaInterface) {
            return null;
        }

        $contentType = $item->getContent()->getContentType();

        return $contentType->getName() ?? '';
    }
}
