<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class EzPublishConverter implements ConverterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $config
     */
    public function __construct(Repository $repository, array $config)
    {
        $this->repository = $repository;
        $this->config = $config;
    }

    /**
     * Returns the selectable flag of the value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return bool
     */
    public function getIsSelectable(ValueInterface $value)
    {
        if (empty($this->config['types'])) {
            return true;
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $value->getValueObject();

        $contentTypeIdentifier = $this->repository->sudo(
            function (Repository $repository) use ($location) {
                return $repository->getContentTypeService()->loadContentType(
                    $location->contentInfo->contentTypeId
                )->identifier;
            }
        );

        return in_array($contentTypeIdentifier, $this->config['types']);
    }
}
