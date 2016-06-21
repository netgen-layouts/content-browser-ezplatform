<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

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
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return array
     */
    public function getValues(ItemInterface $item)
    {
        $content = $this->repository->sudo(
            function (Repository $repository) use ($item) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $this->getContentInfo($item)
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
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo(ItemInterface $item)
    {
        return $item->getLocation()->contentInfo;
    }
}
