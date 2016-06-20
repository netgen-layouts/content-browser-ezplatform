<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Configurator\Handler;

use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
use eZ\Publish\API\Repository\Repository;

class EzLocationConfiguratorHandler implements ConfiguratorHandlerInterface
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
     * Returns if the item is selectable based on provided config.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     * @param array $config
     *
     * @return bool
     */
    public function isSelectable(ItemInterface $item, array $config)
    {
        if (empty($config['types']) || !is_array($config['types'])) {
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

        return in_array($contentTypeIdentifier, $config['types']);
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
        return $item->getValue()->getLocation()->contentInfo;
    }
}
