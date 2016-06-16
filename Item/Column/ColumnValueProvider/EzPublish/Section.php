<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

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
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return mixed
     */
    public function getValue(ValueInterface $value)
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($value) {
                return $repository->getSectionService()->loadSection(
                    $value->getValueObject()->contentInfo->sectionId
                )->name;
            }
        );
    }
}
