<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProvider;

use Netgen\Bundle\ContentBrowserBundle\Item\Renderer\TemplateValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;
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
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return array
     */
    public function getValues(ValueInterface $value)
    {
        $tag = $value->getValueObject();

        return array(
            'tag' => $tag->id > 0 ? $tag : null,
        );
    }
}
