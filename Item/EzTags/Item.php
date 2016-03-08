<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzTags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\Item as BaseItem;

class Item extends BaseItem implements ItemInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array $properties
     */
    public function __construct(Tag $tag, array $properties = array())
    {
        parent::__construct($properties);

        $this->tag = $tag;
    }

    /**
     * Returns the API tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
}
