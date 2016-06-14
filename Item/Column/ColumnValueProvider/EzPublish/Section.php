<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;

class Section implements ColumnValueProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Provides the column value.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $valueObject
     *
     * @return mixed
     */
    public function getValue($valueObject)
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($valueObject) {
                return $repository->getSectionService()->loadSection(
                    $valueObject->contentInfo->sectionId
                )->name;
            }
        );
    }
}
