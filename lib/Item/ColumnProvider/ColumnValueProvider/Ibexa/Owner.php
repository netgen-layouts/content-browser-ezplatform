<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\IbexaInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Owner implements ColumnValueProviderInterface
{
    public function __construct(private Repository $repository) {}

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof IbexaInterface) {
            return null;
        }

        return $this->repository->sudo(
            function () use ($item): string {
                try {
                    return $this->repository->getContentService()->loadContent(
                        $item->getContent()->contentInfo->ownerId,
                    )->getName() ?? '';
                } catch (NotFoundException) {
                    // Owner might be deleted in Ibexa database
                    return '';
                }
            },
        );
    }
}
