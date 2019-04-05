<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use Netgen\ContentBrowser\Ez\Item\EzPublish\EzPublishInterface;
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
        if (!$item instanceof EzPublishInterface) {
            return null;
        }

        return $this->repository->sudo(
            static function (Repository $repository) use ($item): string {
                try {
                    $ownerVersionInfo = $repository->getContentService()->loadVersionInfoById(
                        $item->getContent()->contentInfo->ownerId
                    );
                } catch (NotFoundException $e) {
                    // Owner might be deleted in eZ database
                    return '';
                }

                return $ownerVersionInfo->getName() ?? '';
            }
        );
    }
}
