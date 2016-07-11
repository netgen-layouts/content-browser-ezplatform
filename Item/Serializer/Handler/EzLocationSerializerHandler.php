<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Serializer\Handler;

use Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\Serializer\ItemSerializerHandlerInterface;
use eZ\Publish\API\Repository\Repository;

class EzLocationSerializerHandler implements ItemSerializerHandlerInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface $config
     */
    public function __construct(Repository $repository, ConfigurationInterface $config)
    {
        $this->repository = $repository;
        $this->config = $config;
    }

    /**
     * Returns if the item is selectable.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return bool
     */
    public function isSelectable(ItemInterface $item)
    {
        if (!$this->config->hasParameter('types')) {
            return true;
        }

        $contentTypes = $this->config->getParameter('types');
        if (!is_array($contentTypes) || empty($contentTypes)) {
            return true;
        }

        $contentInfo = $this->getContentInfo($item);

        $contentTypeIdentifier = $this->repository->sudo(
            function (Repository $repository) use ($contentInfo) {
                return $repository->getContentTypeService()->loadContentType(
                    $contentInfo->contentTypeId
                )->identifier;
            }
        );

        return in_array($contentTypeIdentifier, $contentTypes);
    }

    /**
     * Returns the content info value object from provided item.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo(ItemInterface $item)
    {
        return $item->getLocation()->contentInfo;
    }
}
