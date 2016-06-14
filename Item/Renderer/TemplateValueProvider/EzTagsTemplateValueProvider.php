<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

class EzTagsTemplateValueProvider implements TemplateValueProviderInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Provides the values for template rendering.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $valueObject
     *
     * @return array
     */
    public function getValues($valueObject)
    {
        return array(
            'tag' => $valueObject->id > 0 ? $valueObject : null,
        );
    }
}
