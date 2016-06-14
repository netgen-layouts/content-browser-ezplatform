<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter;

use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

class EzTagsConverter implements ConverterInterface
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
     * Returns the value type this converter supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'eztags';
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
        return $value->getValueObject()->id > 0 ? true : false;
    }
}
