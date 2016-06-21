<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class EzContentTemplateValueProvider implements TemplateValueProviderInterface
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
        $contentInfo = $item->getContentInfo();

        $content = $this->repository->sudo(
            function (Repository $repository) use ($contentInfo) {
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
}
