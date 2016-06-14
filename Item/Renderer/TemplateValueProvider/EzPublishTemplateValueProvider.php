<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;

class EzPublishTemplateValueProvider implements TemplateValueProviderInterface
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
     * @param \eZ\Publish\API\Repository\Values\Content\Location $valueObject
     *
     * @return array
     */
    public function getValues($valueObject)
    {
        $content = $this->repository->sudo(
            function (Repository $repository) use ($valueObject) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $valueObject->contentInfo
                );
            }
        );

        return array(
            'content' => $content,
            'location' => $valueObject,
        );
    }
}
