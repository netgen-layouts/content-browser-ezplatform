<?php

namespace Netgen\ContentBrowser\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\Repository;
use Netgen\ContentBrowser\Item\Renderer\TemplateValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

class EzLocationTemplateValueProvider implements TemplateValueProviderInterface
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
     * Provides the values for template rendering.
     *
     * @param \Netgen\ContentBrowser\Item\ItemInterface $item
     *
     * @return array
     */
    public function getValues(ItemInterface $item)
    {
        $contentInfo = $this->getContentInfo($item);

        $content = $this->repository->sudo(
            function (Repository $repository) use ($item, $contentInfo) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $contentInfo
                );
            }
        );

        return array(
            'content' => $content,
            'location' => $item->getLocation(),
        );
    }

    /**
     * Returns the content info value object from provided item.
     *
     * @param \Netgen\ContentBrowser\Item\ItemInterface $item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo(ItemInterface $item)
    {
        return $item->getLocation()->contentInfo;
    }
}
