<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use eZ\Publish\API\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

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
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return array
     */
    public function getValues(ValueInterface $value)
    {
        $location = $value->getValueObject();

        $content = $this->repository->sudo(
            function (Repository $repository) use ($location) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $location->contentInfo
                );
            }
        );

        return array(
            'content' => $content,
            'location' => $location,
        );
    }
}
