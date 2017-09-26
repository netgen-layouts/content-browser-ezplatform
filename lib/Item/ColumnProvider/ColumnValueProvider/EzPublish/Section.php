<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Repository;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzContent\EzContentInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class Section implements ColumnValueProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzContentInterface) {
            return null;
        }

        return $this->repository->sudo(
            function (Repository $repository) use ($item) {
                return $repository->getSectionService()->loadSection(
                    $item->getContent()->contentInfo->sectionId
                )->name;
            }
        );
    }
}
