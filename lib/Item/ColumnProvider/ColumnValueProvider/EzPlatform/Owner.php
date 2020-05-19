<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use Netgen\ContentBrowser\Ez\Item\EzPlatform\EzPlatformInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Owner implements ColumnValueProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzPlatformInterface) {
            return null;
        }

        return $this->repository->sudo(
            static function (Repository $repository) use ($item): string {
                try {
                    $ownerContent = $repository->getContentService()->loadContent(
                        $item->getContent()->contentInfo->ownerId
                    );
                } catch (NotFoundException $e) {
                    // Owner might be deleted in eZ database
                    return '';
                }

                return $ownerContent->getName() ?? '';
            }
        );
    }
}
